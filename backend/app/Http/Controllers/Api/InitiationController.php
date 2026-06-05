<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kickoff;
use App\Models\Stakeholder;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class InitiationController extends Controller
{
    public function stakeholders()
    {
        return response()->json(Stakeholder::latest()->get());
    }

    public function storeStakeholder(Request $request)
    {
        $validated = $request->validate([
            'project_id'   => 'required|exists:projects,id',
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'role'         => 'nullable|string|max:255',
            'influence'    => 'nullable|string|max:50',
            'interest'     => 'nullable|string|max:50',
            'expectations' => 'nullable|string|max:2000',
            'status'       => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(Stakeholder::create($validated), 201);
    }

    public function updateStakeholder(Request $request, Stakeholder $stakeholder)
    {
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone'        => 'nullable|string|max:50',
            'role'         => 'nullable|string|max:255',
            'influence'    => 'nullable|string|max:50',
            'interest'     => 'nullable|string|max:50',
            'expectations' => 'nullable|string|max:2000',
            'status'       => 'nullable|string|max:50',
        ]);

        $stakeholder->update($validated);

        return response()->json($stakeholder);
    }

    public function destroyStakeholder(Stakeholder $stakeholder)
    {
        $stakeholder->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function kickoffs()
    {
        return response()->json(Kickoff::latest()->get());
    }

    public function storeKickoff(Request $request)
    {
        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string|max:2000',
            'meeting_date'    => 'nullable|date',
            'duration_minutes'=> 'nullable|integer|min:1',
            'location'        => 'nullable|string|max:255',
            'agenda'          => 'nullable|string',
            'minutes'         => 'nullable|string',
            'status'          => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(Kickoff::create($validated), 201);
    }
}
