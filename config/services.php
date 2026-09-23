<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Default trial length (spec §54) — Super Admin can override at runtime
    // via system_settings without a deploy; this env value is only the
    // fallback used until that setting exists.
    'trial_days' => env('TRIAL_DAYS', 30),

    // Provider-independent integrations (spec rules 8-9) — never hard-code
    // a specific WhatsApp/AI vendor; the bound implementation is chosen by
    // these env values (see App\Providers\AppServiceProvider bindings added
    // in Phase 9 / Phase 12). Left empty, the safe no-op implementation is
    // used and the corresponding feature stays hidden.
    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER'),
        'api_url' => env('WHATSAPP_API_URL'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER'),
        'api_key' => env('AI_API_KEY'),
    ],

];
