<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/**
 * Profile representation of the authenticated user for the mobile personal
 * account. Exposes only the current user's own data plus activity counters.
 *
 * @mixin User
 */
#[OA\Schema(
    schema: 'Account',
    title: 'Account',
    description: 'The authenticated user profile for the mobile personal account.',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 42),
        new OA\Property(property: 'username', type: 'string', example: 'jane_doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'jane@example.com'),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['user']),
        new OA\Property(property: 'last_activity_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'dialogs_count', type: 'integer', example: 5),
        new OA\Property(property: 'messages_count', type: 'integer', example: 17),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object',
)]
class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->roles->pluck('slug')->all(),
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),
            'dialogs_count' => $this->dialogs_count
                ?? $this->dialogs()->count(),
            'messages_count' => $this->messages_count
                ?? $this->dialogs()->withCount('requestHistories')->get()->sum('request_histories_count'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
