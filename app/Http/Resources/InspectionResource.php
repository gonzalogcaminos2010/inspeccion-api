<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InspectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_item_id,
            'work_order_item_id' => $this->work_order_item_id,
            'template_id' => $this->inspection_template_id,
            'inspection_template_id' => $this->inspection_template_id,
            'equipment_id' => $this->equipment_id,
            'inspector_id' => $this->inspector_id,
            'status' => $this->status,
            'overall_result' => $this->overall_result,
            'final_result' => $this->overall_result,
            'notes' => $this->observations,
            'observations' => $this->observations,
            'score' => $this->score,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'supervisor_notes' => $this->supervisor_notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'template' => new InspectionTemplateResource($this->whenLoaded('template')),
            'answers' => InspectionAnswerResource::collection($this->whenLoaded('answers')),
            'photos' => InspectionPhotoResource::collection($this->whenLoaded('photos')),
            'findings' => FindingResource::collection($this->whenLoaded('findings')),
            'work_order_item' => new WorkOrderItemResource($this->whenLoaded('workOrderItem')),
            'work_order' => new WorkOrderItemResource($this->whenLoaded('workOrderItem')),
            'inspector' => new UserResource($this->whenLoaded('inspector')),
            'approver' => new UserResource($this->whenLoaded('approver')),
            'equipment' => new EquipmentResource($this->whenLoaded('equipment')),
            'signature_data' => $this->inspector_signature ? asset('storage/'.$this->inspector_signature) : null,
            'inspector_signature' => $this->inspector_signature ? asset('storage/'.$this->inspector_signature) : null,
            'inspector_signed_at' => $this->inspector_signed_at,
            'supervisor_signature' => $this->supervisor_signature ? asset('storage/'.$this->supervisor_signature) : null,
            'supervisor_signed_at' => $this->supervisor_signed_at,
            'client_signature' => $this->client_signature ? asset('storage/'.$this->client_signature) : null,
            'client_signed_at' => $this->client_signed_at,
            'certificate_number' => $this->certificate_number,
            'certificate_issued_at' => $this->certificate_issued_at,
            'qr_token' => $this->qr_token,
            'all_signatures_complete' => $this->inspector_signature && $this->supervisor_signature && $this->client_signature,
        ];
    }
}
