<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FindingResource;
use App\Models\Finding;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class FindingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Finding::query()->with('question');

        if ($inspectionId = $request->query('inspection_id')) {
            $query->where('inspection_id', $inspectionId);
        }

        if ($severity = $request->query('severity')) {
            $query->where('severity', $severity);
        }

        if ($request->has('is_resolved')) {
            $query->where('is_resolved', filter_var($request->query('is_resolved'), FILTER_VALIDATE_BOOLEAN));
        }

        $findings = $query->paginate($request->query('per_page', 15));

        return $this->paginated(
            $findings->through(fn ($finding) => new FindingResource($finding)),
            'Findings retrieved successfully'
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'inspection_id' => 'required|exists:inspections,id',
            'template_question_id' => 'nullable|exists:template_questions,id',
            'severity' => 'required|string|max:50',
            'description' => 'required|string',
            'recommendation' => 'required|string',
        ]);

        $finding = Finding::create($validated);

        return $this->success(new FindingResource($finding->load('question')), 'Finding created successfully', 201);
    }

    public function show(Finding $finding)
    {
        $finding->load(['question', 'resolver', 'photos']);

        return $this->success(new FindingResource($finding));
    }

    public function update(Request $request, Finding $finding)
    {
        $validated = $request->validate([
            'inspection_id' => 'sometimes|required|exists:inspections,id',
            'template_question_id' => 'nullable|exists:template_questions,id',
            'severity' => 'sometimes|required|string|max:50',
            'description' => 'sometimes|required|string',
            'recommendation' => 'sometimes|required|string',
            'is_resolved' => 'sometimes|boolean',
        ]);

        if (isset($validated['is_resolved'])) {
            if ($validated['is_resolved'] && ! $finding->is_resolved) {
                $validated['resolved_at'] = now();
                $validated['resolved_by'] = auth()->id();
            } elseif (! $validated['is_resolved'] && $finding->is_resolved) {
                $validated['resolved_at'] = null;
                $validated['resolved_by'] = null;
            }
        }

        $finding->update($validated);

        return $this->success(new FindingResource($finding), 'Finding updated successfully');
    }

    public function destroy(Finding $finding)
    {
        $finding->delete();

        return $this->success(null, 'Finding deleted successfully');
    }
}
