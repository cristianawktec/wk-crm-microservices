<?php


use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerDashboardController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SellerController;
use App\Models\User;
use App\Http\Controllers\Api\NotificationController;

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

// Test endpoint for customer app - creates/returns test user with token
// Use ?role=admin to get admin user instead of customer
Route::get('/auth/test-customer', function () {
    $role = request()->query('role', 'customer');
    $email = $role === 'admin' ? 'admin-test@wkcrm.local' : 'customer-test@wkcrm.local';
    $name = $role === 'admin' ? 'Admin WK' : 'Customer Test';
    
    $user = User::firstOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'role' => $role,
            'password' => Hash::make('password123')
        ]
    );
    
    $token = $user->createToken('test-token')->plainTextToken;
    
    return response()->json([
        'success' => true,
        'user' => $user,
        'token' => $token
    ]);
});

// CRUD routes with authentication - Protegido com auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Auth - Verificação de token
    Route::get('/auth/user', [AuthController::class, 'me']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('customers/{customer}', [CustomerController::class, 'show']);
    Route::post('customers', [CustomerController::class, 'store']);
    Route::put('customers/{customer}', [CustomerController::class, 'update']);
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy']);

    // Leads CRUD
    Route::get('leads/sources', [LeadController::class, 'sources']);
    Route::get('leads', [LeadController::class, 'index']);
    Route::get('leads/{lead}', [LeadController::class, 'show']);
    Route::post('leads', [LeadController::class, 'store']);
    Route::put('leads/{lead}', [LeadController::class, 'update']);
    Route::delete('leads/{lead}', [LeadController::class, 'destroy']);

    // Sellers CRUD
    Route::get('sellers/roles', [SellerController::class, 'roles']);
    Route::get('sellers', [SellerController::class, 'index']);
    Route::get('sellers/{seller}', [SellerController::class, 'show']);
    Route::post('sellers', [SellerController::class, 'store']);
    Route::put('sellers/{seller}', [SellerController::class, 'update']);
    Route::delete('sellers/{seller}', [SellerController::class, 'destroy']);

    // Opportunities CRUD
    Route::get('opportunities', [OpportunityController::class, 'index']);
    Route::get('opportunities/{opportunity}', [OpportunityController::class, 'show']);
    Route::post('opportunities', [OpportunityController::class, 'store']);
    Route::put('opportunities/{opportunity}', [OpportunityController::class, 'update']);
    Route::delete('opportunities/{opportunity}', [OpportunityController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/sales-pipeline', [DashboardController::class, 'salesPipeline']);
    Route::get('/vendedores', [DashboardController::class, 'vendedores']);
    Route::post('/simulate-update', [DashboardController::class, 'simulateUpdate']);

    // Customer Dashboard (Portal do Cliente)
    Route::get('/dashboard/customer-stats', [CustomerDashboardController::class, 'getStats']);
    Route::get('/profile', [CustomerDashboardController::class, 'getProfile']);
    Route::put('/profile', [CustomerDashboardController::class, 'updateProfile']);
    Route::get('/customer-opportunities', [CustomerDashboardController::class, 'getOpportunities']);
    Route::post('/customer-opportunities', [CustomerDashboardController::class, 'createOpportunity']);
    Route::put('/customer-opportunities/{opportunity}', [CustomerDashboardController::class, 'updateOpportunity']);
    Route::delete('/customer-opportunities/{opportunity}', [CustomerDashboardController::class, 'deleteOpportunity']);

    // Reports
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/leads', [ReportController::class, 'leadsReport']);
    
    // Analytics Dashboard
    Route::get('/analytics/kpis', [ReportController::class, 'dashboardKpis']);
    Route::get('/analytics/monthly-sales', [ReportController::class, 'monthlySalesTrend']);
    Route::get('/analytics/status-distribution', [ReportController::class, 'statusDistribution']);
    Route::get('/analytics/top-sellers', [ReportController::class, 'topSellersAnalytics']);
    Route::get('/analytics/sales-funnel', [ReportController::class, 'salesFunnelAnalytics']);
    Route::get('/analytics/summary', [ReportController::class, 'analyticalSummary']);

    // Autenticação - Endpoints Protegidos
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
});

// Notifications endpoints
// Index/unread are temporarily public to debug customer app fetch; mutate operations stay protected
Route::middleware(\App\Http\Middleware\CorsMiddleware::class)->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
});

// SSE stream de notificações autenticado via token na query string
// Middleware CorsMiddleware já adiciona headers CORS
Route::get('/notifications/stream', [NotificationController::class, 'stream'])
    ->middleware(\App\Http\Middleware\CorsMiddleware::class)
    ->withoutMiddleware('auth:sanctum');

// SSE Test endpoint (no auth for debugging)
Route::get('/notifications/test-stream', function () {
    set_time_limit(0);
    ob_implicit_flush(true);
    
    return response()->stream(function () {
        // Send initial connected message
        echo "data: " . json_encode(['type' => 'connected', 'message' => 'Test SSE working!']) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        
        // Send 5 heartbeat messages
        for ($i = 1; $i <= 5; $i++) {
            sleep(2);
            echo "data: " . json_encode(['type' => 'heartbeat', 'count' => $i]) . "\n\n";
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        }
        
        echo "data: " . json_encode(['type' => 'done', 'message' => 'Test complete']) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'X-Accel-Buffering' => 'no',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type',
    ]);
});
