<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role ?? 'practitioner',
            'cabinet_id' => $this->cabinet_id,
            'cabinet_name' => $this->cabinet?->name ?? $this->cabinet_name ?? ($this->isSuperAdmin() ? null : 'Cabinet Dentaire'),
            'cabinet_room' => $this->cabinet_room ?? ($this->isSuperAdmin() ? null : 'Fauteuil 1'),
            'cabinet' => $this->cabinet ? [
                'id' => $this->cabinet->id,
                'name' => $this->cabinet->name,
                'address' => $this->cabinet->address,
                'phone' => $this->cabinet->phone,
                'email' => $this->cabinet->email,
            ] : null,
            'is_active' => (bool) ($this->is_active ?? true),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
