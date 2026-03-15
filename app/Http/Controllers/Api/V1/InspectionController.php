<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FindingResource;
use App\Http\Resources\InspectionAnswerResource;
use App\Http\Resources\InspectionPhotoResource;
use App\Http\Resources\InspectionResource;
use App\Models\Inspection;
use App\Models\TemplateQuestion;
use App\Models\WorkOrderItem;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $workOrderItem = WorkOrderItem::findOrFail($validated['work_order_item_id']);

        $inspection = Inspection::create([
            'work_order_item_id' => $workOrderItem->id,
            'inspection_template_id' => $workOrderItem->inspection_template_id,
            'equipment_id' => $workOrderItem->equipment_id,
            'inspector_id' => $request->user()->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

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
            'inspector',
            'equipment',
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

    public function submit(Inspection $inspection)
    {
        if (! in_array($inspection->status, ['in_progress', 'returned'])) {
            return $this->error('Inspection can only be submitted from in_progress or returned status.', 422);
        }

        $inspection->load('answers.question');

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

        $inspection->update([
            'status' => 'submitted',
            'overall_result' => $overallResult,
            'score' => $score,
            'supervisor_notes' => null,
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
        ]);

        $inspection->update([
            'status' => 'completed',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'completed_at' => now(),
            'supervisor_notes' => $validated['supervisor_notes'] ?? null,
            'overall_result' => $validated['final_result'] ?? $inspection->overall_result,
        ]);

        // Now mark the work order item as completed
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
            'equipment',
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
            'photos.*.finding_id' => 'nullable|exists:findings,id',
            'photos.*.caption' => 'nullable|string|max:500',
        ]);

        $createdPhotos = [];

        foreach ($validated['photos'] as $photoData) {
            $path = Storage::disk('public')->putFile('inspections/'.$inspection->id, $photoData['file']);

            $photo = $inspection->photos()->create([
                'photo_path' => $path,
                'template_question_id' => $photoData['template_question_id'] ?? null,
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
            'description' => 'required|string',
            'recommendation' => 'nullable|string',
            'template_question_id' => 'nullable|exists:template_questions,id',
        ]);

        $finding = $inspection->findings()->create($validated);

        return $this->success(new FindingResource($finding), 'Finding created successfully', 201);
    }
}
