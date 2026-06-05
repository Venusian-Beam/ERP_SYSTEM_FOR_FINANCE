<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LlmCoPilotService;
use App\Services\QueryResolverService;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class ConversationalGatewayController extends Controller
{
    public function __construct(
        private readonly LlmCoPilotService $llm,
        private readonly QueryResolverService $resolver,
    ) {}

    public function __invoke(Request $request): Response
    {
        $sender = (string) $request->input('From', '');
        $user = User::query()->where('phone', $sender)->first();

        if ($user === null || $user->tenant_id === null) {
            return response('Sender is not registered for ERP access.', 403)
                ->header('Content-Type', 'text/plain');
        }

        TenantContext::set((int) $user->tenant_id);

        try {
            $message = trim((string) $request->input('Body', ''));

            if (empty($message)) {
                return response('Please provide a query.', 200)
                    ->header('Content-Type', 'text/plain');
            }

            // 1. Send natural language to LLM to get structured JSON intent
            $parsedQuery = $this->llm->parseQuery($message);

            if ($parsedQuery === null || $parsedQuery['intent'] === 'unknown') {
                return response('I could not understand that command. Try "Check status for invoice #1024".', 200)
                    ->header('Content-Type', 'text/plain');
            }

            // 2. Resolve the JSON intent securely against the database
            $result = $this->resolver->resolve($parsedQuery, $message);

            return response($result['reply'], 200)->header('Content-Type', 'text/plain');

        } finally {
            TenantContext::clear();
        }
    }
}
