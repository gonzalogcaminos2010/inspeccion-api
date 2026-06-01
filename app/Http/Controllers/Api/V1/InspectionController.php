<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FindingResource;
use App\Http\Resources\InspectionAnswerResource;
use App\Http\Resources\InspectionPhotoResource;
use App\Http\Resources\InspectionResource;
use App\Models\CategoryEquipmentField;
use App\Models\Equipment;
use App\Models\Inspection;
use App\Models\TemplateCategory;
use App\Models\TemplateQuestion;
use App\Models\WorkOrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InspectionController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Inspection::query()
            ->with(['template', 'equipment', 'inspector']);

        if ($request->has('work_order_id')) {
            $query->whereHas('workOrderItem', fn ($q) => $q->where('work_order_id', $request->query('work_order_id')));
        }

        if ($request->has('inspector_id')) {
            $query->where('inspector_id', $request->query('inspector_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->has('equipment_id')) {
            $query->where('equipment_id', $request->query('equipment_id'));
        }

        $paginator = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $paginator->through(fn ($item) => new InspectionResource($item)),
            'Inspections retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_item_id' => 'required|exists:work_order_items,id',
            'template_id_override' => 'nullable|exists:inspection_templates,id',
        ]);

        $workOrderItem = WorkOrderItem::with('category.defaultTemplate')->findOrFail($validated['work_order_item_id']);

        $templateId = $validated['template_id_override']
            ?? $workOrderItem->inspection_template_id
            ?? $workOrderItem->category?->default_template_id;

        if (! $templateId) {
            return $this->error(
                'No hay plantilla para este ítem: especifique inspection_template_id en la OT, configure default_template_id en la categoría, o pase template_id_override.',
                422
            );
        }

        $inspection = Inspection::create([
            'work_order_item_id' => $workOrderItem->id,
            'inspection_template_id' => $templateId,
            'equipment_id' => $workOrderItem->equipment_id,
            'inspector_id' => $request->user()->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        // Persist the chosen template back on the item (so resume sees the same one).
        if ($workOrderItem->inspection_template_id !== $templateId) {
            $workOrderItem->update(['inspection_template_id' => $templateId]);
        }

        $workOrderItem->update(['status' => 'in_progress']);

        $inspection->load(['template', 'equipment', 'inspector', 'workOrderItem']);

        return $this->success(new InspectionResource($inspection), 'Inspection created successfully', 201);
    }

    public function show(Inspection $inspection)
    {
        $inspection->load([
            'template.sections.questions',
            'answers',
            'photos',
            'findings.photos',
            'workOrderItem.workOrder',
            'workOrderItem.category.equipmentFields',
            'inspector',
            'equipment.category.equipmentFields',
        ]);

        return $this->success(new InspectionResource($inspection));
    }

    public function saveAnswers(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.template_question_id' => 'required|exists:template_questions,id',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.answer_boolean' => 'nullable|boolean',
            'answers.*.answer_number' => 'nullable|numeric',
            'answers.*.answer_json' => 'nullable|array',
            'answers.*.notes' => 'nullable|string',
        ]);

        $savedAnswers = [];

        foreach ($validated['answers'] as $answerData) {
            $question = TemplateQuestion::find($answerData['template_question_id']);

            $isFlagged = false;
            if ($question && $question->type === 'yes_no' && isset($answerData['answer_boolean'])) {
                $boolString = $answerData['answer_boolean'] ? '1' : '0';
                if (is_array($question->fail_values) && in_array($boolString, $question->fail_values)) {
                    $isFlagged = true;
                }
            }

            $answer = $inspection->answers()->updateOrCreate(
                [
                    'inspection_id' => $inspection->id,
                    'template_question_id' => $answerData['template_question_id'],
                ],
                [
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'answer_boolean' => $answerData['answer_boolean'] ?? null,
                    'answer_number' => $answerData['answer_number'] ?? null,
                    'answer_json' => $answerData['answer_json'] ?? null,
                    'notes' => $answerData['notes'] ?? null,
                    'is_flagged' => $isFlagged,
                ]
            );

            $savedAnswers[] = $answer;
        }

        return $this->success(
            InspectionAnswerResource::collection(collect($savedAnswers)),
            'Answers saved successfully'
        );
    }

    /**
     * POST /inspections/{id}/equipment-data
     * Body: { fields: { key_name: value, ... } }
     * Buffers identification data on the inspection. At submit it syncs to equipment.metadata.
     */
    public function saveEquipmentData(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'fields' => 'required|array',
        ]);

        $existing = $inspection->equipment_data ?? [];
        $merged = array_merge($existing, $validated['fields']);

        $inspection->update(['equipment_data' => $merged]);

        return $this->success(['equipment_data' => $merged], 'Equipment data saved');
    }

    public function submit(Request $request, Inspection $inspection)
    {
        if (! in_array($inspection->status, ['in_progress', 'returned'])) {
            return $this->error('Inspection can only be submitted from in_progress or returned status.', 422);
        }

        $inspection->load(['workOrderItem.category.equipmentFields', 'equipment', 'answers.question']);

        // Guard 1: placeholder must be resolved before submit.
        if ($inspection->workOrderItem?->is_equipment_placeholder) {
            return $this->error(
                'El equipo de esta inspección es un placeholder: resuélvalo antes de enviar (POST /work-order-items/{id}/resolve-equipment).',
                422
            );
        }

        // Guard 2: all category required fields must have value in equipment_data OR equipment.metadata.
        $missing = $this->missingRequiredFields($inspection);
        if (! empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'Faltan campos obligatorios de identificación del equipo.',
                'missing_fields' => $missing,
            ], 422);
        }

        // Sync inspection.equipment_data → equipment.metadata (respecting is_mutable).
        $this->syncEquipmentData($inspection);

        // Result calculation (unchanged).
        $yesNoAnswers = $inspection->answers->filter(fn ($a) => $a->question && $a->question->type === 'yes_no');
        $total = $yesNoAnswers->count();
        $flagged = $yesNoAnswers->where('is_flagged', true)->count();

        if ($total > 0) {
            if ($flagged === 0) {
                $overallResult = 'approved';
            } elseif ($flagged <= ($total * 0.3)) {
                $overallResult = 'conditionally_approved';
            } else {
                $overallResult = 'rejected';
            }

            $score = round((($total - $flagged) / $total) * 100);
        } else {
            $overallResult = 'approved';
            $score = 100;
        }

        $updateData = [
            'status' => 'submitted',
            'overall_result' => $overallResult,
            'score' => $score,
            'supervisor_notes' => null,
        ];

        if ($request->filled('notes')) {
            $updateData['observations'] = $request->input('notes');
        }

        $inspection->update($updateData);

        $inspection->load([
            'template.sections.questions',
            'answers',
            'photos',
            'findings.photos',
            'workOrderItem.workOrder',
            'inspector',
            'equipment.category',
        ]);

        return $this->success(new InspectionResource($inspection), 'Inspection submitted successfully. Pending supervisor approval.');
    }

    public function approve(Request $request, Inspection $inspection)
    {
        if ($inspection->status !== 'submitted') {
            return $this->error('Only submitted inspections can be approved.', 422);
        }

        $validated = $request->validate([
            'final_result' => 'nullable|string|in:approved,conditionally_approved,rejected',
            'supervisor_notes' => 'nullable|string',
            'next_inspection_due_at' => 'nullable|date',
        ]);

        $inspection->update([
            'status' => 'completed',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'completed_at' => now(),
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
            'overall_result' => $validated['final_result'] ?? $inspection->overall_result,
        ]);

        $inspection->update([
            'certificate_number' => Inspection::generateCertificateNumber(),
            'certificate_issued_at' => now(),
            'qr_token' => Str::uuid()->toString(),
        ]);

        // Hook: update equipment tracking fields.
        $this->updateEquipmentTracking($inspection, $validated['next_inspection_due_at'] ?? null);

        if ($inspection->workOrderItem) {
            $inspection->workOrderItem->update(['status' => 'completed']);
        } else {
            $inspection->load('workOrderItem');
            $inspection->workOrderItem?->update(['status' => 'completed']);
        }

        $inspection->load([
            'template.sections.questions',
            'answers',
            'photos',
            'findings.photos',
            'workOrderItem.workOrder',
            'inspector',
            'equipment.category',
            'approver',
        ]);

        return $this->success(new InspectionResource($inspection), 'Inspection approved successfully.');
    }

    public function returnInspection(Request $request, Inspection $inspection)
    {
        if ($inspection->status !== 'submitted') {
            return $this->error('Only submitted inspections can be returned.', 422);
        }

        $validated = $request->validate([
            'supervisor_notes' => 'required|string',
        ]);

        $inspection->update([
            'status' => 'returned',
            'supervisor_notes' => $validated['supervisor_notes'],
        ]);

        $inspection->load([
            'template.sections.questions',
            'answers',
            'photos',
            'findings.photos',
            'workOrderItem.workOrder',
            'inspector',
            'equipment',
        ]);

        return $this->success(new InspectionResource($inspection), 'Inspection returned to inspector.');
    }

    public function uploadPhotos(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*.file' => 'required|image|max:5120',
            'photos.*.template_question_id' => 'nullable|exists:template_questions,id',
            'photos.*.answer_id' => 'nullable|exists:template_questions,id',
            'photos.*.finding_id' => 'nullable|exists:findings,id',
            'photos.*.caption' => 'nullable|string|max:500',
        ]);

        $createdPhotos = [];

        foreach ($validated['photos'] as $photoData) {
            $path = Storage::disk('public')->putFile('inspections/'.$inspection->id, $photoData['file']);

            $photo = $inspection->photos()->create([
                'photo_path' => $path,
                'template_question_id' => $photoData['template_question_id'] ?? $photoData['answer_id'] ?? null,
                'finding_id' => $photoData['finding_id'] ?? null,
                'caption' => $photoData['caption'] ?? null,
            ]);

            $createdPhotos[] = $photo;
        }

        return $this->success(
            InspectionPhotoResource::collection(collect($createdPhotos)),
            'Photos uploaded successfully',
            201
        );
    }

    public function createFinding(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'severity' => 'required|string',
            'description' => 'nullable|string',
            'title' => 'nullable|string',
            'recommendation' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'template_question_id' => 'nullable|exists:template_questions,id',
            'answer_id' => 'nullable|exists:template_questions,id',
        ]);

        $finding = $inspection->findings()->create([
            'severity' => $validated['severity'],
            'description' => $validated['description'] ?? $validated['title'] ?? '',
            'recommendation' => $validated['recommendation'] ?? $validated['corrective_action'] ?? null,
            'template_question_id' => $validated['template_question_id'] ?? $validated['answer_id'] ?? null,
        ]);

        return $this->success(new FindingResource($finding), 'Finding created successfully', 201);
    }

    public function reopen(Request $request, Inspection $inspection)
    {
        if ($request->user()->id !== $inspection->inspector_id) {
            return $this->error('Solo el inspector dueño de la inspección puede reabrirla.', 403);
        }

        if (! in_array($inspection->status, ['submitted', 'returned'])) {
            return $this->error("No se puede reabrir: la inspección está en estado {$inspection->status}.", 409);
        }

        $inspection->update([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        $inspection->load([
            'template.sections.questions',
            'answers',
            'photos',
            'findings.photos',
            'workOrderItem.workOrder',
            'inspector',
            'equipment',
        ]);

        return $this->success(new InspectionResource($inspection), 'Inspección reabierta correctamente.');
    }

    public function sign(Request $request, Inspection $inspection)
    {
        $validated = $request->validate([
            'role' => 'required|string|in:inspector,supervisor,client',
            'signature' => 'required|string',
        ]);

        $role = $validated['role'];

        if (! in_array($inspection->status, ['submitted', 'completed'])) {
            return $this->error('Signatures can only be added to submitted or completed inspections.', 422);
        }

        $signatureField = $role.'_signature';
        if ($inspection->{$signatureField}) {
            return $this->error("The {$role} signature has already been recorded.", 422);
        }

        $user = $request->user();

        if ($role === 'inspector' && $user->id !== $inspection->inspector_id) {
            return $this->error('Only the assigned inspector can sign as inspector.', 403);
        }

        if ($role === 'supervisor' && ! in_array($user->role, ['supervisor', 'admin'])) {
            return $this->error('Only supervisors or admins can sign as supervisor.', 403);
        }

        $base64 = $validated['signature'];
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $imageData = base64_decode($base64);

        if ($imageData === false) {
            return $this->error('Invalid base64 signature data.', 422);
        }

        $directory = 'signatures/'.$inspection->id;
        $filename = $role.'.png';
        Storage::disk('public')->put($directory.'/'.$filename, $imageData);

        $inspection->update([
            $signatureField => $directory.'/'.$filename,
            $role.'_signed_at' => now(),
        ]);

        $inspection->load(['template', 'equipment', 'inspector', 'approver']);

        return $this->success(new InspectionResource($inspection), ucfirst($role).' signature recorded successfully.');
    }

    /**
     * Returns labels of required category equipment fields that are still empty
     * across both inspection.equipment_data and equipment.metadata.
     */
    protected function missingRequiredFields(Inspection $inspection): array
    {
        $category = $inspection->workOrderItem?->category ?? $inspection->equipment?->category;
        if (! $category) {
            return [];
        }

        $required = $category->equipmentFields()->where('is_required', true)->get();
        $buffer = $inspection->equipment_data ?? [];
        $persisted = $inspection->equipment?->metadata ?? [];

        return $required
            ->filter(function (CategoryEquipmentField $field) use ($buffer, $persisted) {
                $value = $buffer[$field->key_name] ?? $persisted[$field->key_name] ?? null;

                return $value === null || $value === '';
            })
            ->map(fn (CategoryEquipmentField $field) => [
                'key_name' => $field->key_name,
                'label' => $field->label,
            ])
            ->values()
            ->all();
    }

    /**
     * Sync inspection.equipment_data → equipment.metadata.
     * - is_mutable=true: always overwritten with the new value.
     * - is_mutable=false: written only if the equipment had no value yet (first capture).
     * - If `proxima_inspeccion` (date) is present, also set equipment.next_inspection_due_at.
     */
    protected function syncEquipmentData(Inspection $inspection): void
    {
        $equipment = $inspection->equipment;
        $category = $inspection->workOrderItem?->category ?? $equipment?->category;

        if (! $equipment || ! $category) {
            return;
        }

        $buffer = $inspection->equipment_data ?? [];
        if (empty($buffer)) {
            return;
        }

        $fields = $category->equipmentFields()->get()->keyBy('key_name');
        $metadata = $equipment->metadata ?? [];
        $nextDue = null;

        foreach ($buffer as $key => $value) {
            $field = $fields->get($key);
            if (! $field) {
                continue;
            }

            $currentValue = $metadata[$key] ?? null;

            if ($field->is_mutable || $currentValue === null || $currentValue === '') {
                $metadata[$key] = $value;
            }

            if ($key === 'proxima_inspeccion' && $value) {
                $nextDue = $value;
            }
        }

        $updates = ['metadata' => $metadata];
        if ($nextDue) {
            $updates['next_inspection_due_at'] = $nextDue;
        }

        $equipment->update($updates);
    }

    /**
     * On approve, stamp equipment tracking columns and auto-compute next_inspection_due_at
     * when neither the inspector nor the supervisor provided one.
     */
    protected function updateEquipmentTracking(Inspection $inspection, ?string $supervisorOverride): void
    {
        $equipment = $inspection->equipment;
        if (! $equipment) {
            return;
        }

        $category = $inspection->workOrderItem?->category ?? $equipment->category;

        $updates = [
            'last_inspection_completed_at' => $inspection->approved_at ?? now(),
            'last_inspection_id' => $inspection->id,
        ];

        if ($supervisorOverride) {
            $updates['next_inspection_due_at'] = $supervisorOverride;
        } elseif (! $equipment->next_inspection_due_at && $category?->default_inspection_interval_months) {
            $base = $inspection->approved_at ?? now();
            $updates['next_inspection_due_at'] = Carbon::parse($base)
                ->addMonths((int) $category->default_inspection_interval_months)
                ->toDateString();
        }

        $equipment->update($updates);
    }
}
