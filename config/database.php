<?php

use Illuminate\Support\Str;

$database = [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'uais'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];

$databases = explode(',', env('DB_DATABASE', ''));
unset($database['connections']['sqlsrv']);
foreach ($databases as $db) {
    $database['connections'][$db] = [
        'driver' => 'sqlsrv',
        'url' => env('DATABASE_URL'),
        'host' => env('DB_HOST', '192.168.74.71'),
        'port' => env('DB_PORT', '1433'),
        'database' => $db,
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'encrypt' => 'no',
    ];
}

$databases = explode(',', env('DB_DATABASE_57', ''));
foreach ($databases as $db) {
    $database['connections'][$db] = [
        'driver' => 'mysql',
        'url' => env('DATABASE_URL_57'),
        'host' => env('DB_HOST_57', '192.168.74.57'),
        'port' => env('DB_PORT_57', '3306'),
        'database' => $db,
        'username' => env('DB_USERNAME_57', 'forge'),
        'password' => env('DB_PASSWORD_57', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ];
}
$database['connections']['lms'] = [
    'driver' => 'mysql',
    'url' => env('DATABASE_URL_LMS'),
    'host' => env('DB_HOST_LMS', '192.168.74.105'),
    'port' => env('DB_PORT_LMS', '3306'),
    'database' => env('DB_DATABASE_LMS', 'moodle'),
    'username' => env('DB_USERNAME_LMS', 'forge'),
    'password' => env('DB_PASSWORD_LMS', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
];
$databases = explode(',', env('DB_DATABASE_91', ''));
foreach ($databases as $db) {
    $database['connections'][$db] = [
        'driver' => 'sqlsrv',
        'url' => env('DATABASE_URL_91'),
        'host' => env('DB_HOST_91', '192.168.74.91'),
        'port' => env('DB_PORT_91', '1433'),
        'database' => $db,
        'username' => env('DB_USERNAME_91', 'forge'),
        'password' => env('DB_PASSWORD_91', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'encrypt' => 'no',
    ];
}
$databases = explode(',', env('DB_DATABASE_124', ''));
foreach ($databases as $db) {
    $database['connections'][$db] = [
        'driver' => 'pgsql',
        'url' => env('DATABASE_URL_124'),
        'host' => env('DB_HOST_124', '127.0.0.1'),
        'port' => env('DB_PORT_124', '5432'),
        'database' => $db,
        'username' => env('DB_USERNAME_124', 'forge'),
        'password' => env('DB_PASSWORD_124', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
    ];
}

$databases = explode(',', env('DB_DATABASE_54', ''));
foreach ($databases as $db) {
    $database['connections'][$db] = [
        'driver' => 'sqlsrv',
        'url' => env('DATABASE_URL_54'),
        'host' => env('DB_HOST_54', '192.168.74.54'),
        'port' => env('DB_PORT_54', '1433'),
        'database' => $db,
        'username' => env('DB_USERNAME_54', 'forge'),
        'password' => env('DB_PASSWORD_54', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'encrypt' => 'no',
    ];
}
return $database;
