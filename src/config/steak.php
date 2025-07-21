<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Steak Core Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình chính cho thư viện Steak Core
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình database cho các bảng của Steak Core
    |
    */
    'database' => [
        'connection' => env('STEAK_DB_CONNECTION', 'default'),
        'prefix' => env('STEAK_TABLE_PREFIX', 'steak_'),
        'migrations' => [
            'auto_load' => env('STEAK_AUTO_LOAD_MIGRATIONS', true),
            'publish_path' => env('STEAK_MIGRATIONS_PATH', 'database/migrations'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cache cho Steak Core
    |
    */
    'cache' => [
        'default_ttl' => env('STEAK_CACHE_TTL', 3600),
        'driver' => env('STEAK_CACHE_DRIVER', 'file'),
        'prefix' => env('STEAK_CACHE_PREFIX', 'steak_'),
        'compress' => env('STEAK_CACHE_COMPRESS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | ShortCode Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho ShortCode Engine
    |
    */
    'shortcode' => [
        'enabled' => env('STEAK_SHORTCODE_ENABLED', true),
        'cache_enabled' => env('STEAK_SHORTCODE_CACHE', true),
        'cache_ttl' => env('STEAK_SHORTCODE_CACHE_TTL', 1800),
        'ignore_html' => env('STEAK_SHORTCODE_IGNORE_HTML', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Management Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho File Management System
    |
    */
    'files' => [
        'upload_path' => env('STEAK_UPLOAD_PATH', 'uploads'),
        'max_size' => env('STEAK_MAX_FILE_SIZE', 10485760), // 10MB
        'allowed_types' => env('STEAK_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt'),
        'log_operations' => env('STEAK_LOG_FILE_OPERATIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình bảo mật cho Steak Core
    |
    */
    'security' => [
        'encrypt_sensitive_data' => env('STEAK_ENCRYPT_SENSITIVE', true),
        'log_security_events' => env('STEAK_LOG_SECURITY', true),
        'rate_limiting' => [
            'enabled' => env('STEAK_RATE_LIMITING', true),
            'max_attempts' => env('STEAK_MAX_ATTEMPTS', 60),
            'decay_minutes' => env('STEAK_DECAY_MINUTES', 1),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho Laravel Octane compatibility
    |
    */
    'octane' => [
        'enabled' => env('STEAK_OCTANE_ENABLED', false),
        'reset_static_state' => env('STEAK_RESET_STATIC_STATE', true),
        'reset_services_state' => env('STEAK_RESET_SERVICES_STATE', true),
        'memory_limit' => env('STEAK_MEMORY_LIMIT', '512M'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình logging cho Steak Core
    |
    */
    'logging' => [
        'enabled' => env('STEAK_LOGGING_ENABLED', true),
        'channel' => env('STEAK_LOG_CHANNEL', 'daily'),
        'level' => env('STEAK_LOG_LEVEL', 'info'),
        'max_files' => env('STEAK_LOG_MAX_FILES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình performance cho Steak Core
    |
    */
    'performance' => [
        'query_cache' => env('STEAK_QUERY_CACHE', true),
        'view_cache' => env('STEAK_VIEW_CACHE', true),
        'route_cache' => env('STEAK_ROUTE_CACHE', true),
        'config_cache' => env('STEAK_CONFIG_CACHE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Internationalization Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình đa ngôn ngữ cho Steak Core
    |
    */
    'i18n' => [
        'default_locale' => env('STEAK_DEFAULT_LOCALE', 'vi'),
        'fallback_locale' => env('STEAK_FALLBACK_LOCALE', 'en'),
        'available_locales' => explode(',', env('STEAK_AVAILABLE_LOCALES', 'vi,en')),
        'auto_detect' => env('STEAK_AUTO_DETECT_LOCALE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho API features
    |
    */
    'api' => [
        'rate_limiting' => env('STEAK_API_RATE_LIMITING', true),
        'throttle' => env('STEAK_API_THROTTLE', '60,1'),
        'cors' => [
            'enabled' => env('STEAK_API_CORS', true),
            'origins' => explode(',', env('STEAK_API_CORS_ORIGINS', '*')),
            'methods' => explode(',', env('STEAK_API_CORS_METHODS', 'GET,POST,PUT,DELETE,OPTIONS')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Development Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho development mode
    |
    */
    'development' => [
        'debug_mode' => env('STEAK_DEBUG_MODE', false),
        'show_queries' => env('STEAK_SHOW_QUERIES', false),
        'log_queries' => env('STEAK_LOG_QUERIES', false),
        'profiler' => env('STEAK_PROFILER', false),
    ],
]; 