<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WebhookSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_rejects_invalid_signature(): void
    {
        config(['services.twilio.auth_token' => 'secret']);

        $this->post('/webhooks/conversational/twilio', ['Body' => 'STATUS INVOICE #1024'], [
            'X-Twilio-Signature' => 'invalid',
        ])->assertForbidden();
    }
}
