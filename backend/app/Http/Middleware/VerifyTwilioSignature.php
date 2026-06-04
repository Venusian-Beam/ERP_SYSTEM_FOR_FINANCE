<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class VerifyTwilioSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $authToken = (string) config('services.twilio.auth_token');
        abort_if($authToken === '', 503, 'Twilio auth token is not configured.');

        $signature = (string) $request->headers->get('X-Twilio-Signature', '');
        abort_if($signature === '', 403, 'Missing Twilio signature.');

        $url = $request->fullUrl();
        $params = $request->request->all();
        ksort($params);

        $data = $url;
        foreach ($params as $key => $value) {
            $data .= $key.(string) $value;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $authToken, true));

        abort_unless(hash_equals($expected, $signature), 403, 'Invalid Twilio signature.');

        return $next($request);
    }
}
