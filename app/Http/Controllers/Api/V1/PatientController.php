<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PatientController extends Controller
{
    /**
     * Display a listing of the patients.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search') ?? $request->query('query');

        $patients = Patient::query()
            ->when($search, function ($query, $search) {
                $term = '%'.mb_strtolower((string) $search).'%';
                $query->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(dossier_id) LIKE ?', [$term]);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return PatientResource::collection($patients);
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient): PatientResource
    {
        return new PatientResource($patient);
    }
}
