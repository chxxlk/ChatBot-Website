<?php

function readSecret($paths)
{
    foreach ($paths as $path) {
        if (file_exists($path)) {
            return trim(file_get_contents($path));
        }
    }
    return null;
}
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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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
    'openrouter' => [
        'api_key' => readSecret([
           '/run/secrets/openrouter_key',
            base_path('../secrets/openrouter_key.txt'),
        ]) ? env('OPENROUTER_API_KEY') : null,
        'model' => 'deepseek/deepseek-chat-v3.1:free',
        'base_url' => 'https://openrouter.ai/api/v1',
    ],
    'huggingface' => [
        'api_key' => readSecret([
            '/run/secrets/huggingface_key',
            base_path('../secrets/huggingface_key.txt'),
        ]) ? env('HUGGINGFACE_API_KEY') : null,
        'model' => 'Qwen/Qwen3-Embedding-8B',
        'base_url' => 'https://router.huggingface.co',
    ],

];
