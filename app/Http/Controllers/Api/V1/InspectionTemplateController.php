<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InspectionTemplateResource;
use App\Models\InspectionTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionTemplateController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = InspectionTemplate::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->query('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('vehicle_type')) {
            $query->where('vehicle_type', $request->query('vehicle_type'));
        }

        $paginator = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $paginator->through(fn ($item) => new InspectionTemplateResource($item)),
            'Inspection templates retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:inspection_templates,code',
            'description' => 'nullable|string',
            'vehicle_type' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'sections' => 'nullable|array',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.order' => 'required|integer',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.text' => 'required|string',
            'sections.*.questions.*.type' => 'required|string',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.order' => 'required|integer',
            'sections.*.questions.*.fail_values' => 'nullable|array',
        ]);

        $template = DB::transaction(function () use ($validated) {
            $template = InspectionTemplate::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'description' => $validated['description'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            if (! empty($validated['sections'])) {
                foreach ($validated['sections'] as $sectionData) {
                    $section = $template->sections()->create([
                        'name' => $sectionData['name'],
                        'order' => $sectionData['order'],
                        'description' => $sectionData['description'] ?? null,
                    ]);

                    if (! empty($sectionData['questions'])) {
                        foreach ($sectionData['questions'] as $questionData) {
                            $section->questions()->create([
                                'text' => $questionData['text'],
                                'type' => $questionData['type'],
                                'options' => $questionData['options'] ?? null,
                                'is_required' => $questionData['is_required'] ?? true,
                                'order' => $questionData['order'],
                                'fail_values' => $questionData['fail_values'] ?? null,
                            ]);
                        }
                    }
                }
            }

            return $template;
        });

        $template->load('sections.questions');

        return $this->success(new InspectionTemplateResource($template), 'Inspection template created successfully', 201);
    }

    public function show(InspectionTemplate $inspectionTemplate)
    {
        $inspectionTemplate->load([
            'sections' => fn ($q) => $q->orderBy('order'),
            'sections.questions' => fn ($q) => $q->orderBy('order'),
        ]);

        return $this->success(new InspectionTemplateResource($inspectionTemplate));
    }

    public function update(Request $request, InspectionTemplate $inspectionTemplate)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:100|unique:inspection_templates,code,'.$inspectionTemplate->id,
            'description' => 'nullable|string',
            'vehicle_type' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'sections' => 'nullable|array',
            'sections.*.id' => 'nullable|integer',
            'sections.*.name' => 'required|string|max:255',
            'sections.*.order' => 'required|integer',
            'sections.*.description' => 'nullable|string',
            'sections.*.questions' => 'nullable|array',
            'sections.*.questions.*.id' => 'nullable|integer',
            'sections.*.questions.*.text' => 'required|string',
            'sections.*.questions.*.type' => 'required|string',
            'sections.*.questions.*.options' => 'nullable|array',
            'sections.*.questions.*.is_required' => 'nullable|boolean',
            'sections.*.questions.*.order' => 'required|integer',
            'sections.*.questions.*.fail_values' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $inspectionTemplate) {
            $inspectionTemplate->update(collect($validated)->except('sections')->toArray());

            if (array_key_exists('sections', $validated)) {
                $incomingSectionIds = collect($validated['sections'])
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                // Delete sections not in the request
                $inspectionTemplate->sections()
                    ->whereNotIn('id', $incomingSectionIds)
                    ->delete();

                foreach ($validated['sections'] as $sectionData) {
                    if (! empty($sectionData['id'])) {
                        // Update existing section
                        $section = $inspectionTemplate->sections()->find($sectionData['id']);
                        if ($section) {
                            $section->update([
                                'name' => $sectionData['name'],
                                'order' => $sectionData['order'],
                                'description' => $sectionData['description'] ?? null,
                            ]);
                        }
                    } else {
                        // Create new section
                        $section = $inspectionTemplate->sections()->create([
                            'name' => $sectionData['name'],
                            'order' => $sectionData['order'],
                            'description' => $sectionData['description'] ?? null,
                        ]);
                    }

                    if ($section && isset($sectionData['questions'])) {
                        $incomingQuestionIds = collect($sectionData['questions'])
                            ->pluck('id')
                            ->filter()
                            ->toArray();

                        // Delete questions not in the request
                        $section->questions()
                            ->whereNotIn('id', $incomingQuestionIds)
                            ->delete();

                        foreach ($sectionData['questions'] as $questionData) {
                            if (! empty($questionData['id'])) {
                                $question = $section->questions()->find($questionData['id']);
                                if ($question) {
                                    $question->update([
                                        'text' => $questionData['text'],
                                        'type' => $questionData['type'],
                                        'options' => $questionData['options'] ?? null,
                                        'is_required' => $questionData['is_required'] ?? true,
                                        'order' => $questionData['order'],
                                        'fail_values' => $questionData['fail_values'] ?? null,
                                    ]);
                                }
                            } else {
                                $section->questions()->create([
                                    'text' => $questionData['text'],
                                    'type' => $questionData['type'],
                                    'options' => $questionData['options'] ?? null,
                                    'is_required' => $questionData['is_required'] ?? true,
                                    'order' => $questionData['order'],
                                    'fail_values' => $questionData['fail_values'] ?? null,
                                ]);
                            }
                        }
                    }
                }
            }
        });

        $inspectionTemplate->load('sections.questions');

        return $this->success(new InspectionTemplateResource($inspectionTemplate), 'Inspection template updated successfully');
    }

    public function destroy(InspectionTemplate $inspectionTemplate)
    {
        $inspectionTemplate->delete();

        return $this->success(null, 'Inspection template deleted successfully');
    }

    public function duplicate(InspectionTemplate $template)
    {
        $newTemplate = DB::transaction(function () use ($template) {
            $template->load('sections.questions');

            $newTemplate = $template->replicate();
            $newTemplate->code = $template->code.'-COPY-'.time();
            $newTemplate->version = ($template->version ?? 0) + 1;
            $newTemplate->is_active = false;
            $newTemplate->save();

            foreach ($template->sections as $section) {
                $newSection = $newTemplate->sections()->create([
                    'name' => $section->name,
                    'order' => $section->order,
                    'description' => $section->description,
                ]);

                foreach ($section->questions as $question) {
                    $newSection->questions()->create([
                        'text' => $question->text,
                        'type' => $question->type,
                        'options' => $question->options,
                        'is_required' => $question->is_required,
                        'order' => $question->order,
                        'fail_values' => $question->fail_values,
                    ]);
                }
            }

            return $newTemplate;
        });

        $newTemplate->load('sections.questions');

        return $this->success(new InspectionTemplateResource($newTemplate), 'Inspection template duplicated successfully', 201);
    }
}
