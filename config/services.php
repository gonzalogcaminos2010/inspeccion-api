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

    // Photo defect analysis. AI_PROVIDER picks the adapter: 'gemini' (Google AI
    // Studio, free tier, cheapest) or 'anthropic'. Each reads its own key/model.
    'ai' => [
        'provider' => env('AI_PROVIDER', 'gemini'),
        'photo_analysis_enabled' => env('AI_PHOTO_ANALYSIS_ENABLED', true),
    ],

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'photo_analysis_enabled' => env('AI_PHOTO_ANALYSIS_ENABLED', true),
        'model' => env('AI_PHOTO_ANALYSIS_MODEL', 'claude-sonnet-4-6'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],

];
