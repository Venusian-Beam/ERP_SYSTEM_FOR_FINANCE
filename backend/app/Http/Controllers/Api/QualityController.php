<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChangeLog;
use App\Models\QaTest;
use App\Models\Risk;
use App\Support\TenantContext;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    public function testCases()
    {
        return response()->json(QaTest::latest()->get());
    }

    public function storeTestCase(Request $request)
    {
        $validated = $request->validate([
            'project_id'     => 'required|exists:projects,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'test_type'      => 'nullable|string|max:255',
            'steps'          => 'nullable|string',
            'expected_result'=> 'nullable|string|max:2000',
            'status'         => 'nullable|string|max:50',
            'assigned_to'    => 'nullable|string|max:255',
            'priority'       => 'nullable|string|max:50',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(QaTest::create($validated), 201);
    }

    public function updateTestCase(Request $request, QaTest $qaTest)
    {
        $validated = $request->validate([
            'title'          => 'sometimes|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'test_type'      => 'nullable|string|max:255',
            'steps'          => 'nullable|string',
            'expected_result'=> 'nullable|string|max:2000',
            'status'         => 'nullable|string|max:50',
            'assigned_to'    => 'nullable|string|max:255',
            'priority'       => 'nullable|string|max:50',
        ]);

        $qaTest->update($validated);

        return response()->json($qaTest);
    }

    public function destroyTestCase(QaTest $qaTest)
    {
        $qaTest->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function runTestCase(Request $request, QaTest $qaTest)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:passed,failed,blocked,retest',
        ]);

        $qaTest->update(['status' => $validated['status']]);

        return response()->json($qaTest);
    }

    public function risks()
    {
        return response()->json(Risk::latest()->get());
    }

    public function storeRisk(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title'      => 'required|string|max:255',
            'description'=> 'nullable|string|max:2000',
            'probability'=> 'nullable|integer|min:1|max:5',
            'impact'     => 'nullable|integer|min:1|max:5',
            'severity'   => 'nullable|string|max:50',
            'mitigation' => 'nullable|string|max:2000',
            'contingency'=> 'nullable|string|max:2000',
            'status'     => 'nullable|string|max:50',
            'owner'      => 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(Risk::create($validated), 201);
    }

    public function updateRisk(Request $request, Risk $risk)
    {
        $validated = $request->validate([
            'title'      => 'sometimes|string|max:255',
            'description'=> 'nullable|string|max:2000',
            'probability'=> 'nullable|integer|min:1|max:5',
            'impact'     => 'nullable|integer|min:1|max:5',
            'severity'   => 'nullable|string|max:50',
            'mitigation' => 'nullable|string|max:2000',
            'contingency'=> 'nullable|string|max:2000',
            'status'     => 'nullable|string|max:50',
            'owner'      => 'nullable|string|max:255',
        ]);

        $risk->update($validated);

        return response()->json($risk);
    }

    public function changeLogs()
    {
        return response()->json(ChangeLog::latest()->get());
    }

    public function storeChangeLog(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'change_type' => 'nullable|string|max:255',
            'priority'    => 'nullable|string|max:50',
            'status'      => 'nullable|string|max:50',
            'requested_by'=> 'nullable|string|max:255',
        ]);

        $validated['tenant_id'] = TenantContext::requireId();

        return response()->json(ChangeLog::create($validated), 201);
    }

    public function approveChangeLog(Request $request, ChangeLog $changeLog)
    {
        $validated = $request->validate([
            'approved_by' => 'required|string|max:255',
        ]);

        $changeLog->update([
            'status'      => 'approved',
            'approved_by' => $validated['approved_by'],
            'approved_at' => now(),
        ]);

        return response()->json($changeLog);
    }

    public function rejectChangeLog(ChangeLog $changeLog)
    {
        $changeLog->update(['status' => 'rejected']);

        return response()->json($changeLog);
    }
}
