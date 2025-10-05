<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the application.
    |
    */

    'rate_limiting' => [
        // API rate limiting
        'api' => [
            'requests_per_minute' => env('API_RATE_LIMIT', 60),
            'requests_per_hour' => env('API_RATE_LIMIT_HOURLY', 1000),
        ],

        // Authentication rate limiting
        'auth' => [
            'requests_per_minute' => env('AUTH_RATE_LIMIT', 5),
        ],

        // File upload rate limiting
        'uploads' => [
            'requests_per_minute' => env('UPLOAD_RATE_LIMIT', 10),
        ],
    ],

    'encryption' => [
        // Fields that should be encrypted in the database
        'encrypted_fields' => [
            'users' => [
                'bank_account_number',
                'bank_routing_number',
                'bank_account_holder_name',
                'bank_name',
                'bank_branch',
            ],
        ],
    ],

    'sensitive_data' => [
        // Fields that should be hidden when serializing models
        'hidden_fields' => [
            'users' => [
                'password',
                'remember_token',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'bank_account_number',
                'bank_routing_number',
                'bank_account_holder_name',
                'bank_name',
                'bank_branch',
            ],
        ],
    ],
];