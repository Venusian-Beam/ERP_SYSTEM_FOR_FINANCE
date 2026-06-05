<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectTask;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller
{
    public function index(Request $request)
    {
        $query = ProjectTask::with('project')->latest();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|integer',
            'title'      => 'required|string|max:255',
            'status'     => 'nullable|string',
            'priority'   => 'nullable|string|in:low,medium,high',
            'due_date'   => 'nullable|date',
            'progress'   => 'nullable|integer|min:0|max:100',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();
        $validated['status']   = $validated['status'] ?? 'pending';
        $validated['priority'] = $validated['priority'] ?? 'medium';
        $validated['progress'] = $validated['progress'] ?? 0;

        $task = ProjectTask::create($validated);
        $task->load('project');

        return response()->json($task, 201);
    }

    public function update(Request $request, ProjectTask $projectTask)
    {
        $validated = $request->validate([
            'title'    => 'sometimes|string|max:255',
            'status'   => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $projectTask->update($validated);

        return response()->json($projectTask);
    }

    public function destroy(ProjectTask $projectTask)
    {
        $projectTask->delete();

        return response()->json(['message' => 'Task permanently deleted']);
    }
}
