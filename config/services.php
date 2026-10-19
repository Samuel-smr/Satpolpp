<?php

return [

    'eprofil' => [
        'url' => env('api_eprofil'),
        'token' => env('token_eprofil'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'enabled' => env('AI_ENABLED', true),
        'hourly_limit' => env('AI_HOURLY_LIMIT', 60),
        'daily_limit' => env('AI_DAILY_LIMIT', 1000),
        'key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL', 'gemini-3.5-flash'),
        'base_url' => env('AI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
    ],

    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'local'),
        'bot_url' => env('WHATSAPP_BOT_URL', 'http://localhost:3456'),
        'bot_token' => env('WHATSAPP_BOT_TOKEN'),
    ],

    'skm' => [
        'survey_url' => env('SKM_SURVEY_URL', 'https://skm.go.id/share/instansi/f1319c33-2d84-4f64-bcdb-e73685895e37/1'),
    ],

];
