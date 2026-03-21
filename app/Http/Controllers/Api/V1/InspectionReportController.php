<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class InspectionReportController extends Controller
{
    use ApiResponse;

    public function show(Inspection $inspection)
    {
        if (! in_array($inspection->status, ['submitted', 'completed'])) {
            return $this->error(
                'Reports can only be generated for submitted or completed inspections.',
                422
            );
        }

        $data = $this->prepareReportData($inspection);

        $overallResultLabel = match ($inspection->overall_result) {
            'approved' => 'FAVORABLE',
            'rejected' => 'NO FAVORABLE',
            'conditionally_approved' => 'FAVORABLE CON OBSERVACIONES',
            default => strtoupper($inspection->overall_result ?? 'N/A'),
        };

        $pdf = Pdf::loadView('reports.informe-preliminar', [
            ...$data,
            'overallResultLabel' => $overallResultLabel,
            'isPreview' => false,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('informe-preliminar-'.$inspection->id.'.pdf');
    }

    public function preview(Inspection $inspection)
    {
        $data = $this->prepareReportData($inspection);

        // Calculate preliminary result without saving to DB
        $overallResultLabel = match ($inspection->overall_result) {
            'approved' => 'FAVORABLE',
            'rejected' => 'NO FAVORABLE',
            'conditionally_approved' => 'FAVORABLE CON OBSERVACIONES',
            default => null,
        };

        if (! $overallResultLabel) {
            $hasFlagged = $inspection->answers->contains('is_flagged', true);
            if ($inspection->answers->isEmpty()) {
                $overallResultLabel = '— (en progreso)';
            } elseif ($hasFlagged) {
                $overallResultLabel = 'PENDIENTE DE REVISIÓN';
            } else {
                $overallResultLabel = 'FAVORABLE (preliminar)';
            }
        }

        $pdf = Pdf::loadView('reports.informe-preliminar', [
            ...$data,
            'overallResultLabel' => $overallResultLabel,
            'isPreview' => true,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('preview-informe-'.$inspection->id.'.pdf');
    }

    public function certificate(Inspection $inspection)
    {
        if ($inspection->status !== 'completed' || ! $inspection->certificate_number) {
            return $this->error(
                'Certificates can only be generated for completed inspections with an issued certificate.',
                422
            );
        }

        $data = $this->prepareReportData($inspection);
        $qrCodeDataUri = $this->generateQrCode($inspection);

        $pdf = Pdf::loadView('reports.certificado-inspeccion', [
            ...$data,
            'qrCodeDataUri' => $qrCodeDataUri,
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('certificado-'.$inspection->certificate_number.'.pdf');
    }

    private function generateQrCode(Inspection $inspection): string
    {
        $url = config('app.url').'/api/v1/public/inspections/'.$inspection->qr_token;

        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 5,
            'outputBase64' => true,
        ]);

        return (new QRCode($options))->render($url);
    }

    private function prepareReportData(Inspection $inspection): array
    {
        $inspection->load([
            'template.sections.questions',
            'inspector',
            'equipment.client',
            'workOrderItem.workOrder.inspectionRequest.client',
            'answers.question.section',
            'findings',
            'approver',
        ]);

        $client = $inspection->equipment?->client
            ?? $inspection->workOrderItem?->workOrder?->inspectionRequest?->client;

        // Build signature absolute paths for DomPDF
        $signatures = [];
        foreach (['inspector', 'supervisor', 'client'] as $role) {
            $field = $role.'_signature';
            if ($inspection->{$field}) {
                $path = storage_path('app/public/'.$inspection->{$field});
                if (file_exists($path)) {
                    $signatures[$role] = $path;
                }
            }
        }

        // Group answers by section
        $answersBySection = [];
        foreach ($inspection->answers as $answer) {
            $sectionName = $answer->question?->section?->name ?? 'General';
            $answersBySection[$sectionName][] = $answer;
        }

        return [
            'inspection' => $inspection,
            'client' => $client,
            'equipment' => $inspection->equipment,
            'inspector' => $inspection->inspector,
            'template' => $inspection->template,
            'signatures' => $signatures,
            'answersBySection' => $answersBySection,
        ];
    }
}
