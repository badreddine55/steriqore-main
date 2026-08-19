<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\LabelResource;
use App\Http\Resources\UsageResource;
use App\Models\InstrumentUsage;
use App\Models\Label;
use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LabelController extends Controller
{
    /**
     * Display a listing of the labels.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->query('status');
        $search = $request->query('search') ?? $request->query('query');

        $labels = Label::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, function ($query, $search) {
                $term = '%'.mb_strtolower((string) $search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(code) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(product_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(lot_number) LIKE ?', [$term]);
                });
            })
            ->orderByDesc('id')
            ->get();

        return LabelResource::collection($labels);
    }

    /**
     * Look up and display the label by code or ID.
     */
    public function show(string $code): JsonResponse
    {
        $label = Label::where('code', $code)
            ->orWhere('id', is_numeric($code) ? (int) $code : null)
            ->first();

        if (! $label) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found. Please check the code and try again.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => (new LabelResource($label))->resolve(),
        ]);
    }

    /**
     * Record usage for the specified label on a patient.
     */
    public function recordUsage(Request $request, string $labelId): JsonResponse
    {
        $label = Label::where('id', is_numeric($labelId) ? (int) $labelId : null)
            ->orWhere('code', $labelId)
            ->first();

        if (! $label) {
            return response()->json([
                'status' => 'error',
                'message' => 'Item not found. Please check the code and try again.',
            ], 404);
        }

        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'idempotency_key' => ['required', 'string', 'max:255'],
            'used_at' => ['nullable', 'date'],
            'procedure_type' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Check if previously recorded with the same idempotency key (idempotent retry)
        $existingUsage = InstrumentUsage::where('idempotency_key', $validated['idempotency_key'])->first();
        if ($existingUsage) {
            return response()->json([
                'status' => 'success',
                'message' => 'Usage record retrieved (idempotent).',
                'data' => (new UsageResource($existingUsage))->resolve(),
            ]);
        }

        // Validate label status
        if ($label->isRecalled()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This instrument has been recalled and cannot be used.',
                'recall_reason' => $label->recall_reason,
            ], 410);
        }

        if ($label->isExpired()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This instrument is expired and cannot be used.',
            ], 410);
        }

        if ($label->isAlreadyUsed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This instrument has already been recorded as used.',
                'used_by_patient_name' => $label->used_by_patient_name,
                'used_at' => $label->used_at?->toIso8601String(),
            ], 409);
        }

        $patient = Patient::findOrFail($validated['patient_id']);
        $usedAt = isset($validated['used_at']) ? now()->parse($validated['used_at']) : now();

        $usage = InstrumentUsage::create([
            'label_id' => $label->id,
            'patient_id' => $patient->id,
            'user_id' => $request->user()?->id,
            'idempotency_key' => $validated['idempotency_key'],
            'used_at' => $usedAt,
            'procedure_type' => $validated['procedure_type'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Transition label status to already_used
        $label->update([
            'status' => 'already_used',
            'used_at' => $usedAt,
            'used_by_patient_id' => $patient->id,
            'used_by_patient_name' => $patient->full_name,
        ]);

        // Update patient last visit date
        $patient->update([
            'last_visit' => $usedAt->format('Y-m-d'),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Instrument usage recorded successfully.',
            'data' => (new UsageResource($usage))->resolve(),
        ], 201);
    }
}
