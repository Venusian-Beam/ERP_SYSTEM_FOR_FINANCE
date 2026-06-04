<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ProjectWorkspaceController extends Controller
{
    public function index(Request $request): Response
    {
        $projects = Project::query()
            ->withCount('tasks')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Project $project): Response
    {
        return Inertia::render('Projects/Show', [
            'project' => $project->load('tasks'),
        ]);
    }
}
