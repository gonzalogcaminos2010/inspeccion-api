<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Traits\ApiResponse;

class PublicInspectionController extends Controller
{
    use ApiResponse;

    public function show(string $qrToken)
    {
        $inspection = Inspection::where('qr_token', $qrToken)
            ->where('status', 'completed')
            ->with(['equipment', 'inspector', 'approver', 'template'])
            ->first();

        if (! $inspection) {
            return $this->error('Certificate not found or inspection not completed.', 404);
        }

        return $this->success([
            'certificate_number' => $inspection->certificate_number,
            'certificate_issued_at' => $inspection->certificate_issued_at,
            'status' => $inspection->status,
            'overall_result' => $inspection->overall_result,
            'score' => $inspection->score,
            'equipment' => [
                'name' => $inspection->equipment?->name,
                'brand' => $inspection->equipment?->brand,
                'model' => $inspection->equipment?->model,
                'serial_number' => $inspection->equipment?->serial_number,
                'year' => $inspection->equipment?->year,
                'internal_code' => $inspection->equipment?->internal_code,
            ],
            'template' => $inspection->template?->name,
            'inspector' => $inspection->inspector?->name,
            'approved_by' => $inspection->approver?->name,
            'approved_at' => $inspection->approved_at,
            'completed_at' => $inspection->completed_at,
        ], 'Certificate verified successfully.');
    }
}
