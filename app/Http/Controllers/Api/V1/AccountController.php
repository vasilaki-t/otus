<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ChangePasswordRequest;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\V1\AccountResource;
use App\Http\Resources\V1\DialogResource;
use App\Models\RequestHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

/**
 * Personal account API for the mobile application.
 *
 * Every endpoint is protected by the Passport-backed "auth:api" guard and is
 * scoped strictly to the currently authenticated user — a user can only read
 * and modify their own data (resolved from $request->user()).
 */
class AccountController extends Controller
{
    /**
     * Profile of the currently authenticated user with activity counters.
     */
    #[OA\Get(
        path: '/api/v1/account/profile',
        operationId: 'v1AccountProfile',
        summary: 'Authenticated user profile',
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The authenticated user profile.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Account'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
        ],
    )]
    public function profile(Request $request): AccountResource
    {
        return new AccountResource($this->withCounters($request->user()));
    }

    /**
     * Update the authenticated user's username and/or email.
     */
    #[OA\Put(
        path: '/api/v1/account/profile',
        operationId: 'v1AccountUpdateProfile',
        summary: 'Update profile',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/UpdateProfileInput')),
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profile updated.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Account'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());
        $user->save();

        return (new AccountResource($this->withCounters($user->refresh())))
            ->additional(['message' => 'Профиль обновлён.'])
            ->response();
    }

    /**
     * Change the authenticated user's password.
     */
    #[OA\Put(
        path: '/api/v1/account/password',
        operationId: 'v1AccountChangePassword',
        summary: 'Change password',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ChangePasswordInput')),
        tags: ['Account'],
        responses: [
            new OA\Response(response: 200, description: 'Password changed.'),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 422, description: 'Validation error / wrong current password.'),
        ],
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->password = $request->validated('password');
        $user->save();

        return response()->json(['message' => 'Пароль изменён.']);
    }

    /**
     * Paginated list of the authenticated user's own dialogs.
     */
    #[OA\Get(
        path: '/api/v1/account/dialogs',
        operationId: 'v1AccountDialogs',
        summary: 'Own dialogs',
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated collection of the user dialogs.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Dialog')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
        ],
    )]
    public function dialogs(Request $request): AnonymousResourceCollection
    {
        $dialogs = $request->user()
            ->dialogs()
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return DialogResource::collection($dialogs);
    }

    /**
     * Activity summary for the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/account/stats',
        operationId: 'v1AccountStats',
        summary: 'Activity summary',
        security: [['bearerAuth' => []]],
        tags: ['Account'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Activity summary for the authenticated user.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', properties: [
                        new OA\Property(property: 'dialogs_count', type: 'integer', example: 5),
                        new OA\Property(property: 'messages_count', type: 'integer', example: 17),
                        new OA\Property(property: 'last_activity_at', type: 'string', format: 'date-time', nullable: true),
                    ], type: 'object'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
        ],
    )]
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'dialogs_count' => $this->dialogsCount($user),
                'messages_count' => $this->messagesCount($user),
                'last_activity_at' => $user->last_activity_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * Attach computed activity counters to the user for the resource.
     */
    private function withCounters(User $user): User
    {
        $user->setAttribute('dialogs_count', $this->dialogsCount($user));
        $user->setAttribute('messages_count', $this->messagesCount($user));
        $user->loadMissing('roles');

        return $user;
    }

    private function dialogsCount(User $user): int
    {
        return $user->dialogs()->count();
    }

    /**
     * Number of messages (request histories) across all of the user's dialogs.
     */
    private function messagesCount(User $user): int
    {
        return RequestHistory::query()
            ->whereHas('dialog', fn ($query) => $query->where('user_id', $user->id))
            ->count();
    }
}
