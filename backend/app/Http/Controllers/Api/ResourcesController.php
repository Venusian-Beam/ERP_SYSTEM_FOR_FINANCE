<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetExpense;
use App\Models\Milestone;
use App\Models\TeamMember;
use App\Models\TimeEntry;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class ResourcesController extends Controller
{
    public function members()
    {
        return response()->json(TeamMember::latest()->get());
    }

    public function storeMember(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name'       => 'required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'role'       => 'nullable|string|max:255',
            'hourly_rate'=> 'nullable|numeric|min:0',
            'avatar_url' => 'nullable|string|max:2048',
            'status'     => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(TeamMember::create($validated), 201);
    }

    public function updateMember(Request $request, TeamMember $teamMember)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:50',
            'role'       => 'nullable|string|max:255',
            'hourly_rate'=> 'nullable|numeric|min:0',
            'avatar_url' => 'nullable|string|max:2048',
            'status'     => 'nullable|string|max:50',
        ]);

        $teamMember->update($validated);

        return response()->json($teamMember);
    }

    public function destroyMember(TeamMember $teamMember)
    {
        $teamMember->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function timeEntries()
    {
        return response()->json(TimeEntry::with('teamMember')->latest()->get());
    }

    public function storeTimeEntry(Request $request)
    {
        $validated = $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'team_member_id'=> 'required|exists:team_members,id',
            'description'   => 'nullable|string|max:1000',
            'hours'         => 'required|numeric|min:0',
            'date'          => 'required|date',
            'billable'      => 'nullable|boolean',
            'approved'      => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(TimeEntry::create($validated), 201);
    }

    public function updateTimeEntry(Request $request, TimeEntry $timeEntry)
    {
        $validated = $request->validate([
            'team_member_id'=> 'sometimes|exists:team_members,id',
            'description'   => 'nullable|string|max:1000',
            'hours'         => 'sometimes|numeric|min:0',
            'date'          => 'sometimes|date',
            'billable'      => 'nullable|boolean',
            'approved'      => 'nullable|boolean',
        ]);

        $timeEntry->update($validated);

        return response()->json($timeEntry);
    }

    public function destroyTimeEntry(TimeEntry $timeEntry)
    {
        $timeEntry->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function milestones()
    {
        return response()->json(Milestone::latest()->get());
    }

    public function storeMilestone(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name'       => 'required|string|max:255',
            'description'=> 'nullable|string|max:2000',
            'due_date'   => 'nullable|date',
            'status'     => 'nullable|string|max:50',
            'progress'   => 'nullable|integer|min:0|max:100',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(Milestone::create($validated), 201);
    }

    public function updateMilestone(Request $request, Milestone $milestone)
    {
        $validated = $request->validate([
            'name'       => 'sometimes|string|max:255',
            'description'=> 'nullable|string|max:2000',
            'due_date'   => 'nullable|date',
            'status'     => 'nullable|string|max:50',
            'progress'   => 'nullable|integer|min:0|max:100',
        ]);

        $milestone->update($validated);

        return response()->json($milestone);
    }

    public function destroyMilestone(Milestone $milestone)
    {
        $milestone->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function budget()
    {
        return response()->json(BudgetExpense::latest()->get()->groupBy('category'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category'   => 'required|string|max:255',
            'description'=> 'nullable|string|max:2000',
            'amount'     => 'required|numeric|min:0',
            'date'       => 'required|date',
            'approved'   => 'nullable|boolean',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(BudgetExpense::create($validated), 201);
    }
}
