<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'DialogInput',
    title: 'DialogInput',
    description: 'Payload for creating or updating a dialog.',
    required: ['title'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Onboarding chat'),
    ],
    type: 'object',
)]
class DialogApiRequest extends FormRequest
{
    /**
     * Authorization is enforced by the route guard and the controller policy.
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
        return [
            'title' => ['required', 'string', 'max:255'],
        ];
    }
}
