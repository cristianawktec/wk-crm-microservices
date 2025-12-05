<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\OpportunityController;

Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'servico' => 'API WK CRM Laravel',
        'versao' => '1.0.0',
        'timestamp' => now()->toISOString(),
        'versao_php' => PHP_VERSION,
        'versao_laravel' => app()->version(),
        'localizacao' => 'Brasil - São Paulo'
    ]);
});

Route::get('/info', function () {
    return response()->json([
        'mensagem' => 'API WK CRM Laravel - Design Orientado ao Domínio',
        'endpoints' => [
            'saude' => '/api/health',
            'clientes' => '/api/clientes',
            'leads' => '/api/leads',
            'oportunidades' => '/api/oportunidades'
        ],
        'banco_dados' => [
            'conexao' => config('database.default'),
            'host' => config('database.connections.pgsql.host'),
            'porta' => config('database.connections.pgsql.port'),
            'database' => config('database.connections.pgsql.database')
        ],
        'arquitetura' => [
            'padroes' => ['DDD', 'SOLID', 'TDD'],
            'camadas' => ['Domínio', 'Aplicação', 'Infraestrutura'],
            'idioma' => 'Português Brasil'
        ]
    ]);
});


// Rotas RESTful para Customers, Leads e Opportunities
Route::apiResource('customers', CustomerController::class);

// metadata endpoints used by frontend comboboxes
// Define these specific routes before the resource declaration so
// fixed path segments (e.g. /api/leads/sources) are not captured by
// the resource parameter (`/api/leads/{lead}`) which would attempt
// to treat 'sources' as a UUID and cause a 500 error.
Route::get('leads/sources', [\App\Http\Controllers\Api\LeadController::class, 'sources']);
Route::get('sellers/roles', [\App\Http\Controllers\Api\SellerController::class, 'roles']);

Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class);
Route::apiResource('sellers', \App\Http\Controllers\Api\SellerController::class);
Route::apiResource('opportunities', \App\Http\Controllers\Api\OpportunityController::class);

// Endpoint de leads (static placeholder removed - resource controller used)
// Route::get('/leads', function () {
//     // placeholder response removed to allow Api\LeadController@index to handle this route
// });

// Endpoint de oportunidades (static placeholder removed - resource controller used)
// Route::get('/oportunidades', function () {
//     // placeholder removed
// });

// Endpoint para estatísticas do dashboard - agora com dados reais
Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);

// Endpoint para carregar vendedores nos filtros
Route::get('/vendedores', [App\Http\Controllers\Api\DashboardController::class, 'vendedores']);

// Endpoint para simular atualizações WebSocket (desenvolvimento)
Route::post('/simulate-update', [App\Http\Controllers\Api\DashboardController::class, 'simulateUpdate']);

// Endpoint SSE para updates em tempo real
// Temporarily disabled during local development because long-lived SSE
// connections block the single-threaded `php artisan serve` server and
// can cause timeouts for normal API requests. Re-enable when using a
// multi-worker server or moving SSE to a separate process.
// Route::get('/dashboard/stream', [App\Http\Controllers\Api\DashboardController::class, 'streamUpdates']);

// (Removed legacy redirects that caused /api/customers to redirect to /api/clientes)