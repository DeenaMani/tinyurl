<?php

return [
    /*
    |--------------------------------------------------------------------------
    | URL Storage Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for URL storage modes and database
    | connections for the TinyURL application.
    |
    */

    'storage_mode' => '',

    /*
    |--------------------------------------------------------------------------
    | Storage Modes
    |--------------------------------------------------------------------------
    |
    | Available storage modes:
    | - single: Single table (urls)
    | - multi_table: Multiple tables (urls_a_f, urls_g_l, urls_m_r, urls_s_z)
    | - multi_db: Multiple databases (mysql_1, mysql_2, mysql_3, mysql_4)
    |
    */

    'storage_modes' => [
        'single' => [
            'description' => 'Single table storage',
            'table' => 'urls'
        ],
        'multi_table' => [
            'description' => 'Multiple table storage with sharding',
            'tables' => ['urls_a_f', 'urls_g_l', 'urls_m_r', 'urls_s_z']
        ],
        'multi_db' => [
            'description' => 'Multiple database storage with sharding',
            'connections' => ['mysql_1', 'mysql_2', 'mysql_3', 'mysql_4']
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for URL lookups to improve performance
    |
    */

    'cache' => [
        'enabled' => env('URL_CACHE_ENABLED', true),
        'ttl' => env('URL_CACHE_TTL', 3600), // 1 hour in seconds
        'prefix' => 'tinyurl_'
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for URL generation and validation
    |
    */

    'url' => [
        'token_length' => env('URL_TOKEN_LENGTH', 8),
        'token_chars' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',
        'max_attempts' => 10,
        'default_expiration_days' => 1,
        'max_expiration_days' => 365
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Connections for Multi-DB Mode
    |--------------------------------------------------------------------------
    |
    | Database connection configurations for multi-database sharding mode
    |
    */
    'database_connections' =>
    [
        'mysql_1' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'tinyurl_1',
            'username' => 'root',
            'password' => '',
        ],
        'mysql_2' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'tinyurl_2',
            'username' => 'root',
            'password' => '',
        ],
        'mysql_3' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'tinyurl_3',
            'username' => 'root',
            'password' => '',
        ],
        'mysql_4' => [
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'tinyurl_4',
            'username' => 'root',
            'password' => '',
        ],

    ],

];
