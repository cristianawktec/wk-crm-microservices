<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SellerController;

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

// Autenticação - Endpoints Públicos
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Autenticação - Endpoints Protegidos
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Dashboard - Readable by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:view_dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->middleware('permission:view_dashboard');
    Route::get('/dashboard/sales-pipeline', [DashboardController::class, 'salesPipeline'])->middleware('permission:view_dashboard');
    Route::get('/vendedores', [DashboardController::class, 'vendedores']);
    Route::post('/simulate-update', [DashboardController::class, 'simulateUpdate'])->middleware('permission:view_dashboard');

    // Customers CRUD with permission checks
    Route::middleware('permission:read_customers')->group(function () {
        Route::get('customers', [CustomerController::class, 'index']);
        Route::get('customers/{customer}', [CustomerController::class, 'show']);
    });
    Route::post('customers', [CustomerController::class, 'store'])->middleware('permission:create_customers');
    Route::put('customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:update_customers');
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:delete_customers');

    // Leads metadata endpoint (no auth required within protected group)
    Route::get('leads/sources', [LeadController::class, 'sources']);

    // Leads CRUD with permission checks
    Route::middleware('permission:read_leads')->group(function () {
        Route::get('leads', [LeadController::class, 'index']);
        Route::get('leads/{lead}', [LeadController::class, 'show']);
    });
    Route::post('leads', [LeadController::class, 'store'])->middleware('permission:create_leads');
    Route::put('leads/{lead}', [LeadController::class, 'update'])->middleware('permission:update_leads');
    Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->middleware('permission:delete_leads');

    // Sellers metadata and CRUD
    Route::get('sellers/roles', [SellerController::class, 'roles']);
    Route::middleware('permission:read_sellers')->group(function () {
        Route::get('sellers', [SellerController::class, 'index']);
        Route::get('sellers/{seller}', [SellerController::class, 'show']);
    });
    Route::post('sellers', [SellerController::class, 'store'])->middleware('permission:manage_sellers');
    Route::put('sellers/{seller}', [SellerController::class, 'update'])->middleware('permission:manage_sellers');
    Route::delete('sellers/{seller}', [SellerController::class, 'destroy'])->middleware('permission:manage_sellers');

    // Opportunities CRUD with permission checks
    Route::middleware('permission:read_opportunities')->group(function () {
        Route::get('opportunities', [OpportunityController::class, 'index']);
        Route::get('opportunities/{opportunity}', [OpportunityController::class, 'show']);
    });
    Route::post('opportunities', [OpportunityController::class, 'store'])->middleware('permission:create_opportunities');
    Route::put('opportunities/{opportunity}', [OpportunityController::class, 'update'])->middleware('permission:update_opportunities');
    Route::delete('opportunities/{opportunity}', [OpportunityController::class, 'destroy'])->middleware('permission:delete_opportunities');
});
