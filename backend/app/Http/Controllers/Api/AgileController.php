<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgileDefinition;
use App\Models\BacklogItem;
use App\Models\Sprint;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class AgileController extends Controller
{
    public function sprints()
    {
        return response()->json(Sprint::latest()->get());
    }

    public function storeSprint(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name'       => 'required|string|max:255',
            'goal'       => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|string|max:50',
            'velocity'   => 'nullable|numeric|min:0',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(Sprint::create($validated), 201);
    }

    public function updateSprint(Request $request, Sprint $sprint)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'goal'       => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'status'     => 'nullable|string|max:50',
            'velocity'   => 'nullable|numeric|min:0',
        ]);

        $sprint->update($validated);

        return response()->json($sprint);
    }

    public function backlog()
    {
        return response()->json(BacklogItem::latest()->get());
    }

    public function storeBacklogItem(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'sprint_id'   => 'nullable|exists:sprints,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'story_points'=> 'nullable|integer|min:0',
            'priority'    => 'nullable|string|max:50',
            'status'      => 'nullable|string|max:50',
            'type'        => 'nullable|string|max:255',
            'assignee'    => 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(BacklogItem::create($validated), 201);
    }

    public function updateBacklogItem(Request $request, BacklogItem $backlogItem)
    {
        $validated = $request->validate([
            'sprint_id'   => 'nullable|exists:sprints,id',
            'title'       => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:2000',
            'story_points'=> 'nullable|integer|min:0',
            'priority'    => 'nullable|string|max:50',
            'status'      => 'nullable|string|max:50',
            'type'        => 'nullable|string|max:255',
            'assignee'    => 'nullable|string|max:255',
        ]);

        $backlogItem->update($validated);

        return response()->json($backlogItem);
    }

    public function destroyBacklogItem(BacklogItem $backlogItem)
    {
        $backlogItem->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function definitions()
    {
        return response()->json(AgileDefinition::latest()->get());
    }

    public function updateDefinitions(Request $request, AgileDefinition $agileDefinition)
    {
        $validated = $request->validate([
            'content'    => 'required|array',
            'updated_by' => 'nullable|string|max:255',
        ]);

        $agileDefinition->update($validated);

        return response()->json($agileDefinition);
    }
}
