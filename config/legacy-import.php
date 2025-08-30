<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Legacy Database Import Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for importing legacy single-tenant MySQL database
    | into the multi-tenant PostgreSQL setup.
    |
    */

    'mysql' => [
        'host' => env('LEGACY_MYSQL_HOST', '107.155.75.50'),
        'port' => env('LEGACY_MYSQL_PORT', '5654'),
        'database' => env('LEGACY_MYSQL_DATABASE', 'default'),
        'username' => env('LEGACY_MYSQL_USERNAME', 'mysql'),
        'password' => env('LEGACY_MYSQL_PASSWORD', 'S8Tz8c5ogcy6ZaSsXaoomwVTuDlLDBiIyWhdFGCLgH0nU3wDFEGUo3J9q5HnfiuK'),
    ],

    'default_client' => [
        'name' => env('LEGACY_CLIENT_NAME', 'Legacy Client'),
        'code' => env('LEGACY_CLIENT_CODE', 'LEGACY'),
        'description' => 'Imported from legacy single-tenant database',
    ],

    'batch_size' => env('LEGACY_IMPORT_BATCH_SIZE', 100),

    'tables' => [
        // Table mapping from MySQL to PostgreSQL models
        'users' => [
            'model' => \App\Models\Accounts\User::class,
            'client_field' => 'client_id',
            'dependencies' => ['roles'],
        ],
        'roles' => [
            'model' => \App\Models\Accounts\Role::class,
            'client_field' => null, // Roles are global
            'dependencies' => [],
        ],
        'takers' => [
            'model' => \App\Models\Takers\Taker::class,
            'client_field' => 'client_id',
            'dependencies' => ['groups'],
        ],
        'groups' => [
            'model' => \App\Models\Takers\Group::class,
            'client_field' => 'client_id',
            'dependencies' => [],
        ],
        'exams' => [
            'model' => \App\Models\Exams\Exam::class,
            'client_field' => 'client_id',
            'dependencies' => [],
        ],
        'questions' => [
            'model' => \App\Models\Exams\Question::class,
            'client_field' => 'client_id',
            'dependencies' => ['exams'],
        ],
        'deliveries' => [
            'model' => \App\Models\Deliveries\Delivery::class,
            'client_field' => 'client_id',
            'dependencies' => ['exams', 'groups'],
        ],
        'attempts' => [
            'model' => \App\Models\Attempts\Attempt::class,
            'client_field' => 'client_id',
            'dependencies' => ['takers', 'deliveries'],
        ],
    ],

    'field_mappings' => [
        // Field mappings for data transformation
        'users' => [
            'name' => 'name',
            'email' => 'email',
            'username' => 'username',
            'password' => 'password',
            'email_verified_at' => 'email_verified_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'takers' => [
            'name' => 'name',
            'reg' => 'reg',
            'email' => 'email',
            'password' => 'password',
            'is_verified' => 'is_verified',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'groups' => [
            'name' => 'name',
            'description' => 'description',
            'code' => 'code',
            'last_taker_code' => 'last_taker_code',
            'closed_at' => 'closed_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'exams' => [
            'name' => 'name',
            'title' => 'title',
            'description' => 'description',
            'is_published' => 'is_published',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'questions' => [
            'question' => 'question',
            'exam_id' => 'exam_id',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'deliveries' => [
            'exam_id' => 'exam_id',
            'group_id' => 'group_id',
            'started_at' => 'started_at',
            'ended_at' => 'ended_at',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
        'attempts' => [
            'taker_id' => 'taker_id',
            'delivery_id' => 'delivery_id',
            'started_at' => 'started_at',
            'finished_at' => 'finished_at',
            'score' => 'score',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ],
    ],

    'role_mappings' => [
        // Map legacy role slugs to new system
        'administrator' => 'administrator',
        'scorer' => 'scorer',
        'committee' => 'scorer', // Map committee to scorer
    ],

    'verification_queries' => [
        // Queries to verify import success
        'users_count' => 'SELECT COUNT(*) as count FROM users WHERE client_id = ?',
        'takers_count' => 'SELECT COUNT(*) as count FROM takers WHERE client_id = ?',
        'exams_count' => 'SELECT COUNT(*) as count FROM exams WHERE client_id = ?',
        'attempts_count' => 'SELECT COUNT(*) as count FROM attempts WHERE client_id = ?',
    ],
];