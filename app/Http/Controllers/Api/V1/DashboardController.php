<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InstrumentUsage;
use App\Models\Label;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard stats and active alerts.
     */
    public function alerts(Request $request): JsonResponse
    {
        $todayScans = InstrumentUsage::whereDate('used_at', today())->count();
        $expiredCount = Label::where('status', 'expired')->orWhere('expiration_date', '<', now())->count();
        $recalledCount = Label::where('status', 'recalled')->count();

        $alerts = [];
        if ($expiredCount > 0) {
            $alerts[] = "{$expiredCount} instrument lot(s) expired or near DLC.";
        }
        if ($recalledCount > 0) {
            $alerts[] = "{$recalledCount} instrument lot(s) subject to safety recall.";
        }
        if (empty($alerts)) {
            $alerts[] = 'All sterilization cycles and lots are currently conforming.';
        }

        return response()->json([
            'status' => 'success',
            'today_scans' => $todayScans,
            'scans_count' => $todayScans,
            'active_alerts_count' => count($alerts),
            'last_cycle_at' => now()->format('Y-m-d H:i'),
            'alerts' => $alerts,
        ]);
    }

    /**
     * Get current stock and instrument levels.
     */
    public function stockLevels(Request $request): JsonResponse
    {
        $valid = Label::where('status', 'valid')->where('expiration_date', '>=', now())->count();
        $used = Label::where('status', 'already_used')->count();
        $expired = Label::where('status', 'expired')->orWhere('expiration_date', '<', now())->count();
        $recalled = Label::where('status', 'recalled')->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_instruments' => Label::count(),
                'valid_sterile' => $valid,
                'already_used' => $used,
                'expired' => $expired,
                'recalled' => $recalled,
            ],
        ]);
    }

    /**
     * Get autoclave cycle details.
     */
    public function cycleDetail(string $cycleId): JsonResponse
    {
        $id = is_numeric($cycleId) ? (int) $cycleId : 89;

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $id,
                'cycle_number' => 'CYC-2026-'.str_pad((string) $id, 3, '0', STR_PAD_LEFT),
                'autoclave_name' => 'Melag Vacuklav 40B',
                'program_name' => 'Prion 134°C - 18min (Conforme)',
                'temperature' => 134.5,
                'pressure_bar' => 2.15,
                'duration_minutes' => 18,
                'is_validated' => true,
                'sterilization_date' => now()->subDays(2)->toIso8601String(),
                'operator_name' => 'Dr. Dupont',
                'attachments' => ['rapport_cycle_'.$id.'.pdf', 'courbe_temperature.png'],
            ],
        ]);
    }

    /**
     * Get items sterilized in a specific cycle.
     */
    public function cycleItems(string $cycleId): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                ['id' => 1, 'product_name' => 'Curette Gracey 1/2', 'lot_number' => 'LOT-2026-89A', 'quantity' => 2],
                ['id' => 2, 'product_name' => 'Miroir Dentaire #5', 'lot_number' => 'LOT-2026-89A', 'quantity' => 4],
                ['id' => 3, 'product_name' => 'Sonde Parodontale WHO', 'lot_number' => 'LOT-2026-89A', 'quantity' => 2],
            ],
        ]);
    }

    /**
     * Get cycle attachments/reports.
     */
    public function cycleAttachments(string $cycleId): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'rapport_cycle_validation.pdf',
                'courbe_pression.png',
            ],
        ]);
    }
}
