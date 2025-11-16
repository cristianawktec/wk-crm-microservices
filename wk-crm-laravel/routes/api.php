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


// Rotas RESTful em Português
Route::apiResource('clientes', CustomerController::class);
Route::apiResource('leads', LeadController::class);
Route::apiResource('oportunidades', OpportunityController::class);

// Endpoint para estatísticas do dashboard - agora com dados reais
Route::get('/dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);

// Endpoint para carregar vendedores nos filtros
Route::get('/vendedores', [App\Http\Controllers\Api\DashboardController::class, 'vendedores']);

// Endpoint para simular atualizações WebSocket (desenvolvimento)
Route::post('/simulate-update', [App\Http\Controllers\Api\DashboardController::class, 'simulateUpdate']);

// Endpoint SSE para updates em tempo real
Route::get('/dashboard/stream', [App\Http\Controllers\Api\DashboardController::class, 'streamUpdates']);

// Redirecionamentos para compatibilidade com inglês (se necessário)
Route::get('/customers', function () {
    return redirect('/api/clientes');
});

Route::get('/opportunities', function () {
    return redirect('/api/oportunidades');
});

Route::get('/customer', function () {
    return redirect('/api/clientes');
});