<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionResource;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\Finding;
use App\Models\Inspection;
use App\Models\WorkOrder;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ApiResponse;

    public function stats()
    {
        $recentInspections = Inspection::with(['equipment', 'inspector', 'template'])
            ->latest()
            ->take(5)
            ->get();

        $approvedCount = Inspection::where('overall_result', 'approved')->count();
        $conditionallyApprovedCount = Inspection::where('overall_result', 'conditionally_approved')->count();
        $rejectedCount = Inspection::where('overall_result', 'rejected')->count();
        $totalWithResult = $approvedCount + $conditionallyApprovedCount + $rejectedCount;

        $inspectionsByMonth = collect();
        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->subMonths($i);
            $monthRows = Inspection::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->get(['overall_result']);

            $inspectionsByMonth->push([
                'month' => $date->format('Y-m'),
                'count' => $monthRows->count(),
                'approved' => $monthRows->where('overall_result', 'approved')->count(),
                'rejected' => $monthRows->where('overall_result', 'rejected')->count(),
            ]);
        }

        return $this->success([
            'total_clients' => Client::count(),
            'total_equipment' => Equipment::count(),
            'total_inspections' => Inspection::count(),
            'pending_work_orders' => WorkOrder::where('status', 'pending')->count(),
            'pending_reviews' => Inspection::where('status', 'submitted')->count(),
            'inspections_this_month' => Inspection::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'recent_inspections' => InspectionResource::collection($recentInspections),
            'inspections_by_month' => $inspectionsByMonth,
            'approval_rate' => $totalWithResult > 0 ? (int) round(($approvedCount / $totalWithResult) * 100) : 0,
            'approved_count' => $approvedCount,
            'conditionally_approved_count' => $conditionallyApprovedCount,
            'rejected_count' => $rejectedCount,
            'critical_findings_open' => Finding::whereRaw('LOWER(severity) = ?', ['critical'])
                ->where('is_resolved', false)
                ->count(),
        ], 'Dashboard stats retrieved successfully');
    }
}
