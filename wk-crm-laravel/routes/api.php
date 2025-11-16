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

// Endpoint de leads
Route::get('/leads', function () {
    return response()->json([
        'dados' => [
            [
                'id' => 1,
                'nome' => 'Ana Oliveira',
                'email' => 'ana.oliveira@prospects.com.br',
                'telefone' => '(21) 96666-6666',
                'empresa' => 'Prospectiva Negócios',
                'fonte' => 'Site',
                'status' => 'novo',
                'interesse' => 'CRM',
                'data_criacao' => '2025-10-17T14:30:00-03:00',
                'cidade' => 'Brasília',
                'estado' => 'DF'
            ]
        ],
        'mensagem' => 'Endpoint de Leads - Implementação DDD pendente',
        'arquitetura' => [
            'padrao' => 'Design Orientado ao Domínio',
            'principios' => ['SOLID', 'TDD'],
            'status' => 'a_ser_implementado',
            'idioma' => 'Português Brasil'
        ]
    ]);
});

// Endpoint de oportunidades
Route::get('/oportunidades', function () {
    return response()->json([
        'dados' => [
            [
                'id' => 1,
                'titulo' => 'Implementação Sistema CRM - Tech Corp',
                'cliente_id' => 1,
                'valor' => 150000.00,
                'moeda' => 'BRL',
                'probabilidade' => 75,
                'status' => 'negociacao',
                'data_fechamento_prevista' => '2025-11-30',
                'vendedor' => 'Carlos Mendes',
                'data_criacao' => '2025-10-15T11:00:00-03:00'
            ]
        ],
        'mensagem' => 'Endpoint de Oportunidades - Implementação DDD pendente', 
        'arquitetura' => [
            'padrao' => 'Design Orientado ao Domínio',
            'principios' => ['SOLID', 'TDD'],
            'status' => 'a_ser_implementado',
            'idioma' => 'Português Brasil'
        ]
    ]);
});

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