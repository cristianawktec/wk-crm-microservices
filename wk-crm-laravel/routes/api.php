<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'service' => 'WK CRM Laravel API',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version()
    ]);
});

Route::get('/info', function () {
    return response()->json([
        'message' => 'WK CRM Laravel API - Domain-Driven Design',
        'endpoints' => [
            'health' => '/api/health',
            'customers' => '/api/customers',
            'leads' => '/api/leads',
            'opportunities' => '/api/opportunities'
        ],
        'database' => [
            'connection' => config('database.default'),
            'host' => config('database.connections.pgsql.host'),
            'port' => config('database.connections.pgsql.port'),
            'database' => config('database.connections.pgsql.database')
        ]
    ]);
});