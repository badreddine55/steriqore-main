<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsageResource;
use App\Models\InstrumentUsage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UsageController extends Controller
{
    /**
     * Display a listing of recorded usages for the authenticated practitioner.
     */
    public function practitionerHistory(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $date = $request->query('date');

        $usages = InstrumentUsage::with(['label', 'patient', 'practitioner'])
            ->when($search, function ($query, $search) {
                $term = '%'.mb_strtolower((string) $search).'%';
                $query->whereHas('patient', function ($q) use ($term) {
                    $q->whereRaw('LOWER(first_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(last_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(dossier_id) LIKE ?', [$term]);
                })->orWhereHas('label', function ($q) use ($term) {
                    $q->whereRaw('LOWER(code) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(product_name) LIKE ?', [$term]);
                });
            })
            ->when($date, function ($query, $date) {
                $query->whereDate('used_at', $date);
            })
            ->orderByDesc('used_at')
            ->get();

        return UsageResource::collection($usages);
    }
}
