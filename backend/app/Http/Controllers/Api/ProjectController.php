<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json(Project::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'code'          => 'nullable|string|max:50',
            'status'        => 'nullable|string',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date',
            'budget_amount' => 'nullable|numeric',
            'progress'      => 'nullable|integer|min:0|max:100',
        ]);

        $validated['tenant_id'] = 1;
        $validated['status'] = $validated['status'] ?? 'planning';
        $validated['progress'] = $validated['progress'] ?? 0;

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    public function show(Project $project)
    {
        return response()->json($project->load('tasks'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'status'        => 'nullable|string',
            'start_date'    => 'nullable|date',
            'due_date'      => 'nullable|date',
            'budget_amount' => 'nullable|numeric',
            'progress'      => 'nullable|integer|min:0|max:100',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Project $project)
    {
        $project->tasks()->delete();
        $project->delete();

        return response()->json(['message' => 'Project permanently deleted']);
    }
}
