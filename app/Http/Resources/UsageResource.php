<?php

namespace App\Http\Resources;

use App\Models\InstrumentUsage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin InstrumentUsage
 */
class UsageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'idempotency_key' => $this->idempotency_key,
            'label_id' => (string) $this->label_id,
            'label_code' => $this->label->code ?? '',
            'code' => $this->label->code ?? '',
            'product_name' => $this->label->product_name ?? 'Dental Instrument',
            'lot_number' => $this->label->lot_number ?? '',
            'patient_id' => (string) $this->patient_id,
            'patient_name' => $this->patient->full_name ?? 'Patient',
            'dossier_id' => $this->patient->dossier_id ?? null,
            'allergies' => $this->patient->allergies ?? [],
            'practitioner_id' => (string) ($this->user_id ?? $request->user()?->id ?? '1'),
            'practitioner_name' => $this->practitioner->name ?? $request->user()?->name ?? 'Dr. Practitioner',
            'used_at' => $this->used_at->toIso8601String(),
            'sync_status' => 'synced',
            'procedure_type' => $this->procedure_type,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
