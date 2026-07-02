<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UpdateProfileInput',
    title: 'UpdateProfileInput',
    description: 'Payload for updating the authenticated user profile.',
    properties: [
        new OA\Property(property: 'username', type: 'string', maxLength: 255, example: 'jane_doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
    ],
    type: 'object',
)]
class UpdateProfileRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'username' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
        ];
    }
}
