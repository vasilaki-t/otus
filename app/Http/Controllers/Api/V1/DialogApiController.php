<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DialogApiRequest;
use App\Http\Resources\V1\DialogResource;
use App\Models\Dialog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

/**
 * Versioned (v1) CRUD API over the authenticated user's dialogs.
 *
 * Treated as the "task" resource exposed to external systems. Every endpoint
 * is protected by the Passport-backed "auth:api" guard and scoped to the
 * dialogs owned by the authenticated user (DialogPolicy).
 */
class DialogApiController extends Controller
{
    /**
     * Paginated list of the authenticated user's dialogs.
     */
    #[OA\Get(
        path: '/api/v1/dialogs',
        operationId: 'v1DialogsIndex',
        summary: 'List dialogs',
        security: [['bearerAuth' => []]],
        tags: ['Dialogs'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated collection of dialogs.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Dialog')),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Dialog::class);

        $dialogs = Dialog::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return DialogResource::collection($dialogs);
    }

    /**
     * Display a single dialog owned by the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/dialogs/{dialog}',
        operationId: 'v1DialogsShow',
        summary: 'Show a dialog',
        security: [['bearerAuth' => []]],
        tags: ['Dialogs'],
        parameters: [
            new OA\Parameter(name: 'dialog', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The requested dialog.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Dialog'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden — not the owner.'),
            new OA\Response(response: 404, description: 'Dialog not found.'),
        ],
    )]
    public function show(Dialog $dialog): DialogResource
    {
        $this->authorize('view', $dialog);

        return new DialogResource($dialog);
    }

    /**
     * Create a new dialog for the authenticated user.
     */
    #[OA\Post(
        path: '/api/v1/dialogs',
        operationId: 'v1DialogsStore',
        summary: 'Create a dialog',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DialogInput')),
        tags: ['Dialogs'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Dialog created.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Dialog'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function store(DialogApiRequest $request): JsonResponse
    {
        $this->authorize('create', Dialog::class);

        $dialog = Dialog::query()->create([
            'user_id' => $request->user()->id,
            'title' => $request->validated('title'),
        ]);

        return (new DialogResource($dialog))
            ->additional(['message' => 'Диалог создан.'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a dialog owned by the authenticated user.
     */
    #[OA\Put(
        path: '/api/v1/dialogs/{dialog}',
        operationId: 'v1DialogsUpdate',
        summary: 'Update a dialog',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/DialogInput')),
        tags: ['Dialogs'],
        parameters: [
            new OA\Parameter(name: 'dialog', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Dialog updated.',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'data', ref: '#/components/schemas/Dialog'),
                ]),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden — not the owner.'),
            new OA\Response(response: 404, description: 'Dialog not found.'),
            new OA\Response(response: 422, description: 'Validation error.'),
        ],
    )]
    public function update(DialogApiRequest $request, Dialog $dialog): DialogResource
    {
        $this->authorize('update', $dialog);

        $dialog->update($request->validated());

        return new DialogResource($dialog->refresh());
    }

    /**
     * Delete a dialog owned by the authenticated user.
     */
    #[OA\Delete(
        path: '/api/v1/dialogs/{dialog}',
        operationId: 'v1DialogsDestroy',
        summary: 'Delete a dialog',
        security: [['bearerAuth' => []]],
        tags: ['Dialogs'],
        parameters: [
            new OA\Parameter(name: 'dialog', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Dialog deleted.'),
            new OA\Response(response: 401, description: 'Unauthenticated.'),
            new OA\Response(response: 403, description: 'Forbidden — not the owner.'),
            new OA\Response(response: 404, description: 'Dialog not found.'),
        ],
    )]
    public function destroy(Dialog $dialog): Response
    {
        $this->authorize('delete', $dialog);

        $dialog->delete();

        return response()->noContent();
    }
}
