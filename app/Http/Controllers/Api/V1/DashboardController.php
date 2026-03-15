<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionResource;
use App\Models\Client;
use App\Models\Equipment;
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
        ], 'Dashboard stats retrieved successfully');
    }
}
