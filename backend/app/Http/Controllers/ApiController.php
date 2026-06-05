<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\LlmCoPilotService;
use App\Services\QueryResolverService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

final class ApiController extends Controller
{
    public function __construct(
        private readonly LlmCoPilotService $llm,
        private readonly QueryResolverService $resolver,
    ) {}

    /**
     * Accept a natural language query and return the LLM's parsed JSON intent.
     */
    public function askAI(Request $request): JsonResponse
    {
        $query = (string) $request->input('query', '');
        if (trim($query) === '') {
            return response()->json(['error' => 'Empty query'], 400);
        }

        $user = $request->user();
        $tenantId = $user?->tenant_id;

        if ($tenantId === null) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        TenantContext::set((int) $tenantId);

        try {
            $parsed = $this->llm->parseQuery($query) ?? [];
            $resolved = $this->resolver->resolve($parsed, $query);

            return response()->json([
                'reply' => $resolved['reply'],
                'result' => [
                    'intent' => $parsed['intent'] ?? 'chat',
                    'target' => $parsed['target'] ?? null,
                    'filters' => $parsed['filters'] ?? [],
                    'facts' => $resolved['facts'],
                    'suggestions' => $resolved['suggestions'],
                ],
            ]);
        } finally {
            TenantContext::clear();
        }
    }
}
