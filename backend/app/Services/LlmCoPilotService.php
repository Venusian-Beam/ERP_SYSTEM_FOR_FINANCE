<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class LlmCoPilotService
{
    /**
     * Parse natural language into a structured JSON query intent using Groq.
     */
    public function parseQuery(string $text): ?array
    {
        $apiKey = config('services.groq.key');
        $model = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
        $baseUrl = rtrim((string) config('services.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        
        if (empty($apiKey)) {
            Log::warning('Groq API key missing. Falling back to local intent parser.');
            return $this->fallbackMockParser($text);
        }

        $schema = [
            'tables' => [
                'supplier_invoices' => ['invoice_number', 'amount', 'status', 'due_date', 'supplier_id'],
                'customer_invoices' => ['invoice_number', 'amount', 'status', 'due_date', 'customer_id'],
                'suppliers' => ['name', 'email', 'status'],
                'customers' => ['name', 'email', 'status'],
                'payments' => ['reference', 'amount', 'paid_at', 'method', 'customer_invoice_id'],
                'bank_accounts' => ['account_name', 'bank_name', 'current_balance', 'available_balance', 'status'],
                'bank_transactions' => ['transaction_date', 'description', 'amount', 'type', 'reconciliation_status'],
                'financial_accounts' => ['code', 'name', 'type', 'normal_balance', 'is_active'],
                'journal_entries' => ['reference', 'posted_at', 'description', 'status'],
                'journal_lines' => ['financial_account_id', 'debit', 'credit', 'memo', 'state'],
                'audit_logs' => ['action', 'auditable_type', 'auditable_id', 'created_at'],
                'company_settings' => ['company_name', 'currency', 'fiscal_year_start'],
                'roles' => ['name', 'slug', 'permissions'],
            ]
        ];

        $systemPrompt = "You are Kedebah Finance Co-Pilot, a warm and concise assistant inside a secure financial ERP.\n"
            . "Return ONLY valid JSON. Do not use markdown.\n"
            . "Extract the user's intent and also write a helpful conversational reply.\n"
            . "Never execute or write SQL. Use the provided schema only.\n"
            . "Schema: " . json_encode($schema) . "\n"
            . "If the user is greeting or chatting, intent must be chat and target null.\n"
            . "Output exact format: {\"intent\":\"chat|query\",\"target\":\"table_name|null\",\"filters\":{\"column\":\"value\"},\"reply\":\"friendly user-facing response\",\"suggestions\":[\"short follow-up\"]}";

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->withToken($apiKey)
                ->acceptJson()
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $text],
                    ],
                    'temperature' => 0.35,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::error('Groq API request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->fallbackMockParser($text);
            }

            $content = $response->json('choices.0.message.content');
            if (empty($content)) {
                return $this->fallbackMockParser($text);
            }
            $decoded = json_decode((string) $content, true);
            if (! is_array($decoded)) {
                return $this->fallbackMockParser($text);
            }

            return $this->normalizeIntent($decoded, $text);
        } catch (\Exception $e) {
            Log::error('Groq exception: ' . $e->getMessage());
            return $this->fallbackMockParser($text);
        }
    }

    private function normalizeIntent(array $intent, string $text): array
    {
        $intentName = in_array($intent['intent'] ?? null, ['chat', 'query'], true)
            ? $intent['intent']
            : 'chat';

        $target = $intent['target'] ?? null;
        if ($target === 'null' || $intentName === 'chat') {
            $target = null;
        }

        return [
            'intent' => $intentName,
            'target' => $target,
            'filters' => is_array($intent['filters'] ?? null) ? $intent['filters'] : [],
            'reply' => (string) ($intent['reply'] ?? $this->fallbackMockParser($text)['reply']),
            'suggestions' => is_array($intent['suggestions'] ?? null)
                ? array_slice($intent['suggestions'], 0, 3)
                : [],
        ];
    }

    private function fallbackMockParser(string $text): array
    {
        $textLower = strtolower($text);

        // 1. Matches "invoice 1024" or "bill 1024"
        if (stripos($textLower, 'invoice') !== false && preg_match('/(?:invoice|bill)\s+#?([a-z0-9-]+)/i', $textLower, $matches)) {
            return [
                'intent' => 'query',
                'target' => 'customer_invoices',
                'filters' => [
                    'invoice_number' => strtoupper($matches[1])
                ],
                'reply' => "I found that you are asking about invoice " . strtoupper($matches[1]) . ". I can pull its status, due date, amount, and customer details for review.",
                'suggestions' => ['Show invoice aging', 'Check related receipt', 'Open invoice detail'],
            ];
        }

        // 2. Matches "pending payables" or "bills"
        if (stripos($textLower, 'payable') !== false || stripos($textLower, 'bill') !== false) {
            return [
                'intent' => 'query',
                'target' => 'supplier_invoices',
                'filters' => [
                    'status' => 'pending'
                ],
                'reply' => 'I can help you review pending payables, including bills awaiting approval, upcoming due dates, and payment priorities.',
                'suggestions' => ['Show overdue bills', 'Summarize payment run', 'List bills due this week'],
            ];
        }

        // 3. Matches "cash balance" or "bank accounts"
        if (stripos($textLower, 'cash') !== false || stripos($textLower, 'balance') !== false || stripos($textLower, 'bank') !== false) {
            return [
                'intent' => 'query',
                'target' => 'bank_accounts',
                'filters' => [
                    'status' => 'active'
                ],
                'reply' => 'I can check active bank accounts and summarize available cash, unreconciled items, and recent bank activity.',
                'suggestions' => ['Show cash position', 'Review reconciliation exceptions', 'Forecast cash runway'],
            ];
        }

        // 4. Matches "vendors" or "suppliers"
        if (stripos($textLower, 'vendor') !== false || stripos($textLower, 'supplier') !== false) {
            return [
                'intent' => 'query',
                'target' => 'vendors',
                'filters' => [],
                'reply' => 'I can help you search vendors, review balances, and identify suppliers with open bills or payment activity.',
                'suggestions' => ['Show vendor balances', 'Find active vendors', 'Review vendor payments'],
            ];
        }

        // Default fallback
        return [
            'intent' => 'chat',
            'target' => null,
            'filters' => [],
            'reply' => "Hi, I'm here. Ask me about invoices, pending bills, vendors, cash balances, reconciliations, or financial reports, and I'll help you narrow it down.",
            'suggestions' => ['Show pending payables', 'What is our cash balance?', 'Find invoice 1024'],
        ];
    }
}
