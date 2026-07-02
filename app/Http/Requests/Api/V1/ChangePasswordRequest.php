<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ChangePasswordInput',
    title: 'ChangePasswordInput',
    description: 'Payload for changing the authenticated user password.',
    required: ['current_password', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'old-secret'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'new-secret-123'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'new-secret-123'),
    ],
    type: 'object',
)]
class ChangePasswordRequest extends FormRequest
{
    /**
     * Authorization is enforced by the "auth:api" route guard; the request is
     * always scoped to the authenticated user via $request->user().
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The "current_password" rule verifies the value against the hash of the
     * currently authenticated user using the "api" guard.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:api'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }
}
