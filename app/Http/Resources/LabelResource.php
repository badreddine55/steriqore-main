<?php

namespace App\Http\Resources;

use App\Models\Label;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Label
 */
class LabelResource extends JsonResource
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
            'cabinet_id' => $this->cabinet_id,
            'code' => $this->code,
            'product_name' => $this->product_name,
            'reference' => $this->reference,
            'lot_number' => $this->lot_number,
            'expiration_date' => $this->expiration_date->toIso8601String(),
            'status' => $this->status,
            'cycle_id' => $this->cycle_id,
            'cycle_number' => $this->cycle_number,
            'autoclave_name' => $this->autoclave_name,
            'sterilization_date' => $this->sterilization_date?->toIso8601String(),
            'recall_reason' => $this->recall_reason,
            'used_at' => $this->used_at?->toIso8601String(),
            'used_by_patient_id' => $this->used_by_patient_id,
            'used_by_patient_name' => $this->used_by_patient_name ?? $this->usedByPatient?->full_name,
            'operator_name' => $this->operator_name,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
