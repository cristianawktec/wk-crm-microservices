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
use App\Http\Controllers\Api\TrendsController;
use App\Http\Controllers\Api\SellerController;
use App\Models\User;
use App\Models\Customer;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AiController;

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
            'password' => Hash::make('password123')
        ]
    );

    // Garante que exista um registro na tabela de clientes com o mesmo email
    $customer = Customer::firstOrCreate(
        ['email' => $email],
        [
            'id' => $user->id,
            'name' => $name,
            'phone' => '000000000'
        ]
    );

    // Assign role if user was just created or doesn't have the role
    if (!$user->hasRole($role)) {
        $user->syncRoles([$role]);
    }

    // Criar oportunidades demo se o usuário não tiver nenhuma
    if ($role === 'customer') {
        $customerId = $customer->id;
        $existingOpps = \App\Models\Opportunity::where('customer_id', $customerId)->count();
        if ($existingOpps === 0) {
            // Criar 4 oportunidades de demonstração
            \App\Models\Opportunity::create([
                'title' => 'Implantação CRM - Fase 1',
                'value' => 45000,
                'status' => 'open',
                'probability' => 40,
                'customer_id' => $customerId,
                'notes' => 'Escopo inicial, aguardando aprovação de proposta.'
            ]);

            \App\Models\Opportunity::create([
                'title' => 'Treinamento Times Comerciais',
                'value' => 18000,
                'status' => 'proposal',
                'probability' => 55,
                'customer_id' => $customerId,
                'notes' => 'Pacote de workshops + playbook de vendas.'
            ]);

            \App\Models\Opportunity::create([
                'title' => 'Consultoria de Processos',
                'value' => 8000,
                'status' => 'open',
                'probability' => 80,
                'customer_id' => $customerId,
                'notes' => 'Mapeamento e otimização do fluxo de vendas.'
            ]);

            \App\Models\Opportunity::create([
                'title' => 'Sistema de Automação',
                'value' => 8000,
                'status' => 'won',
                'probability' => 100,
                'customer_id' => $customerId,
                'notes' => 'Integração com ferramentas de marketing.'
            ]);
        }
    }
    
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

    // AI Analysis
    Route::post('opportunities/{id}/ai-analysis', [AiController::class, 'analyzeOpportunity']);
    Route::get('opportunities/{id}/ai-analysis', [AiController::class, 'getAnalyses']);
    Route::post('ai/chat', [AiController::class, 'chat']);
    Route::get('ai/health', [AiController::class, 'health']);

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
    Route::get('/customer-opportunities/{opportunity}', [CustomerDashboardController::class, 'getOpportunity']);
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

    // AI Insights (Original method in AiController)
    Route::post('/ai/opportunity-insights', [AiController::class, 'opportunityInsights']);

    // Autenticação - Endpoints Protegidos
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    
});

// Public endpoints (sem autenticação necessária)
Route::get('/trends/analyze', [TrendsController::class, 'analyze']);

// Notifications endpoints
// Index/unread are temporarily public to debug customer app fetch; mutate operations stay protected
Route::middleware([\App\Http\Middleware\CorsMiddleware::class])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);
    });
});

// SSE stream de notificações autenticado via token na query string
Route::get('/notifications/stream', [NotificationController::class, 'stream'])
    ->middleware(\App\Http\Middleware\AuthenticateQueryToken::class)
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
// Test email endpoint
Route::get('/test-email', function () {
    try {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'cristian@consultoriawk.com'],
            ['name' => 'Cristian Test', 'password' => bcrypt('password123')]
        );

        $mail = new \App\Mail\NotificationMail(
            '🎯 Nova Oportunidade - Teste',
            'Esta é uma mensagem de teste do sistema de notificações WK CRM.',
            'https://app.consultoriawk.com/opportunities/teste',
            now()->toDateTimeString()
        );

        \Illuminate\Support\Facades\Mail::to($user->email)->send($mail);

        return response()->json([
            'success' => true,
            'message' => 'Email enviado com sucesso!',
            'email' => $user->email,
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Webhook deploy endpoint
Route::get('/deploy', function () {
    $secret = env('DEPLOY_SECRET', 'deploy_secret_123');
    $token = request('token', '');

    if ($token !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        chdir('/root/crm');
        $output = [];
        exec('git pull 2>&1', $output, $returnCode);

        $result = [
            'success' => $returnCode === 0,
            'git_pull' => $output,
            'return_code' => $returnCode
        ];

        if ($returnCode === 0) {
            $cacheOutput = [];
            exec('docker exec wk_crm_laravel php artisan optimize:clear 2>&1', $cacheOutput, $cacheCode);
            $result['optimize_clear'] = $cacheOutput;
        }

        return response()->json($result);
    } catch (\Throwable $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});