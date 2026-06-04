<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RelationalGraphService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class GraphController extends Controller
{
    public function __invoke(Request $request, RelationalGraphService $graph): Response
    {
        $payload = $request->validate([
            'root_type' => ['required', 'string', 'max:255'],
            'root_id' => ['required', 'integer'],
        ]);

        return Inertia::render('KnowledgeGraph/Index', [
            'graph' => $graph->tree($payload['root_type'], (int) $payload['root_id']),
            'root' => $payload,
        ]);
    }
}
