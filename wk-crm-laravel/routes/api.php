<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Endpoint de clientes com dados em português
Route::get('/clientes', function () {
    return response()->json([
        'dados' => [
            [
                'id' => 1,
                'nome' => 'João Silva',
                'email' => 'joao.silva@empresa.com.br',
                'telefone' => '(11) 99999-9999',
                'empresa' => 'Tech Corp Brasil',
                'status' => 'ativo',
                'data_criacao' => '2025-10-01T10:00:00-03:00',
                'cidade' => 'São Paulo',
                'estado' => 'SP'
            ],
            [
                'id' => 2,
                'nome' => 'Maria Santos', 
                'email' => 'maria.santos@digital.com.br',
                'telefone' => '(11) 88888-8888',
                'empresa' => 'Soluções Digitais Ltda',
                'status' => 'ativo',
                'data_criacao' => '2025-10-02T15:30:00-03:00',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ'
            ],
            [
                'id' => 3,
                'nome' => 'Pedro Costa',
                'email' => 'pedro.costa@inovacao.com.br',
                'telefone' => '(85) 77777-7777',
                'empresa' => 'Laboratórios de Inovação',
                'status' => 'ativo',
                'data_criacao' => '2025-10-03T09:15:00-03:00',
                'cidade' => 'Fortaleza',
                'estado' => 'CE'
            ]
        ],
        'meta' => [
            'total' => 3,
            'pagina' => 1,
            'por_pagina' => 10,
            'arquitetura' => 'Implementação DDD em progresso',
            'localizacao' => 'Brasil'
        ]
    ]);
});

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

// Mantendo endpoints em inglês para compatibilidade
Route::get('/customers', function () {
    return redirect('/api/clientes');
});

Route::get('/opportunities', function () {
    return redirect('/api/oportunidades');
});