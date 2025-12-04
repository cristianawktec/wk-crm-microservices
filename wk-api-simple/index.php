<?php
// WK CRM - Simple API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple routing
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query parameters
$uri = parse_url($request_uri, PHP_URL_PATH);

// Simple database simulation (in production, use MySQL)
$data = [
    'customers' => [
        [
            'id' => 1,
            'name' => 'Empresa ABC Ltda',
            'email' => 'contato@empresaabc.com',
            'phone' => '(11) 9999-1234',
            'created_at' => '2024-01-15',
            'status' => 'Ativo'
        ],
        [
            'id' => 2,
            'name' => 'Tech Solutions',
            'email' => 'admin@techsolutions.com',
            'phone' => '(11) 8888-5678',
            'created_at' => '2024-01-14',
            'status' => 'Ativo'
        ],
        [
            'id' => 3,
            'name' => 'StartUp XYZ',
            'email' => 'hello@startupxyz.com',
            'phone' => '(11) 7777-9012',
            'created_at' => '2024-01-13',
            'status' => 'Prospecto'
        ]
    ],
    'opportunities' => [
        [
            'id' => 1,
            'customer_id' => 1,
            'customer_name' => 'Empresa ABC Ltda',
            'title' => 'Sistema CRM Premium',
            'value' => 15000.00,
            'status' => 'Negociação',
            'created_at' => '2024-01-15',
            'expected_close' => '2024-02-15'
        ],
        [
            'id' => 2,
            'customer_id' => 2,
            'customer_name' => 'Tech Solutions',
            'title' => 'Consultoria em IA',
            'value' => 8500.00,
            'status' => 'Proposta Enviada',
            'created_at' => '2024-01-14',
            'expected_close' => '2024-02-10'
        ],
        [
            'id' => 3,
            'customer_id' => 3,
            'customer_name' => 'StartUp XYZ',
            'title' => 'Desenvolvimento Web',
            'value' => 12000.00,
            'status' => 'Qualificação',
            'created_at' => '2024-01-13',
            'expected_close' => '2024-02-20'
        ]
    ],
    'dashboard' => [
        'total_customers' => 2450,
        'monthly_sales' => 85240.00,
        'active_opportunities' => 24,
        'open_tickets' => 6,
        'monthly_chart' => [
            'labels' => ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
            'sales' => [12000, 19000, 15000, 25000, 22000, 30000],
            'opportunities' => [120, 190, 150, 250, 220, 300]
        ]
    ]
];

// Route handler
function routeHandler($uri, $method, $data) {
    switch (true) {
        case $uri === '/api/dashboard' && $method === 'GET':
            return $data['dashboard'];
            
        case $uri === '/api/customers' && $method === 'GET':
            return [
                'success' => true,
                'data' => $data['customers'],
                'total' => count($data['customers'])
            ];
            
        case preg_match('/\/api\/customers\/(\d+)/', $uri, $matches) && $method === 'GET':
            $id = (int)$matches[1];
            $customer = array_filter($data['customers'], fn($c) => $c['id'] === $id);
            if ($customer) {
                return [
                    'success' => true,
                    'data' => array_values($customer)[0]
                ];
            } else {
                http_response_code(404);
                return ['success' => false, 'message' => 'Cliente não encontrado'];
            }
            
        case $uri === '/api/opportunities' && $method === 'GET':
            return [
                'success' => true,
                'data' => $data['opportunities'],
                'total' => count($data['opportunities'])
            ];
            
        case $uri === '/api/customers' && $method === 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $newCustomer = [
                'id' => count($data['customers']) + 1,
                'name' => $input['name'] ?? '',
                'email' => $input['email'] ?? '',
                'phone' => $input['phone'] ?? '',
                'created_at' => date('Y-m-d'),
                'status' => 'Ativo'
            ];
            return [
                'success' => true,
                'message' => 'Cliente criado com sucesso',
                'data' => $newCustomer
            ];
            
        case $uri === '/api/health' && $method === 'GET':
            return [
                'status' => 'ok',
                'service' => 'WK CRM API',
                'version' => '1.0.0',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        default:
            http_response_code(404);
            return [
                'success' => false,
                'message' => 'Endpoint não encontrado',
                'available_endpoints' => [
                    'GET /api/health',
                    'GET /api/dashboard',
                    'GET /api/customers',
                    'GET /api/customers/{id}',
                    'POST /api/customers',
                    'GET /api/opportunities'
                ]
            ];
    }
}

try {
    $response = routeHandler($uri, $method, $data);
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>