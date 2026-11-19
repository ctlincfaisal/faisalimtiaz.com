<?php

return [
    // Set this to the publicly reachable URL of the same app/database that sends emails.
    'tracking_base_url' => env('MARKETING_TRACKING_URL'),
    'auth' => [
        'username' => env('MARKETING_LOGIN_USERNAME'),
        'bootstrap_password' => env('MARKETING_LOGIN_PASSWORD'),
    ],
    'super_admin' => [
        'name' => env('MARKETING_SUPER_ADMIN_NAME', 'Super Admin'),
        'email' => env('MARKETING_SUPER_ADMIN_EMAIL'),
        'password' => env('MARKETING_SUPER_ADMIN_PASSWORD'),
    ],
    'paddle' => [
        'environment' => env('PADDLE_ENV', 'sandbox'),
        'client_token' => env('PADDLE_CLIENT_TOKEN'),
        'api_key' => env('PADDLE_API_KEY', env('PADDLE_SANDBOX_API_KEY')),
        'webhook_secret' => env('PADDLE_NOTIFICATION_WEBHOOK_SECRET'),
        'prices' => [
            'growth' => env('PADDLE_GROWTH_PRICE_ID'),
            'scale' => env('PADDLE_SCALE_PRICE_ID'),
        ],
    ],
    'plans' => [
        'free' => [
            'label' => 'Starter',
            'price' => 0,
            'limits' => [
                'leads' => 100,
                'segments' => 1,
                'websites' => 1,
                'mail_services' => 1,
                'signatures' => 1,
                'sequence_steps' => 1,
                'moderators' => 0,
            ],
        ],
        'growth' => [
            'label' => 'Growth',
            'price' => 12,
            'limits' => [
                'leads' => 2500,
                'segments' => 5,
                'websites' => 3,
                'mail_services' => 5,
                'signatures' => 5,
                'sequence_steps' => 5,
                'moderators' => 3,
            ],
        ],
        'scale' => [
            'label' => 'Scale',
            'price' => 49,
            'limits' => [
                'leads' => null,
                'segments' => null,
                'websites' => 10,
                'mail_services' => null,
                'signatures' => null,
                'sequence_steps' => 8,
                'moderators' => 10,
            ],
        ],
    ],
];
