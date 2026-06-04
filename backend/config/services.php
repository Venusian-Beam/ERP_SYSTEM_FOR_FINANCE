<?php

declare(strict_types=1);

return [
    'twilio' => [
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
    ],
    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
    ],
];
