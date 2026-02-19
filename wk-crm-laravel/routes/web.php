<?php
/**
 * Rotas Web - WK CRM Brasil
 * 
 * Este arquivo configura as rotas web da aplicação.
 * Inclui página inicial da API e documentação.
 * 
 * Arquitetura: DDD + SOLID + TDD
 * Localização: Brasil - Português Brasileiro
 */

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/{any?}', function () {
    $adminIndex = public_path('admin/index.html');
    if (file_exists($adminIndex)) {
        return file_get_contents($adminIndex);
    }

    return view('admin');
})->where('any', '.*');

$renderCustomerApp = function () {
    $customerAppIndex = public_path('customer-app/index.html');

    if (!file_exists($customerAppIndex)) {
        return view('welcome');
    }

    $html = file_get_contents($customerAppIndex);
    $host = request()->getHost();
    $isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

    if ($isLocalhost) {
        $html = str_replace('src="./assets/', 'src="/customer-app/assets/', $html);
        $html = str_replace('href="./assets/', 'href="/customer-app/assets/', $html);
    }

    return response($html);
};

// Vue SPA Customer App fallback route
Route::get('/customer-app/{any?}', function () use ($renderCustomerApp) {
    return $renderCustomerApp();
})->where('any', '.*');

Route::get('/assets/{path}', function ($path) {
    $host = request()->getHost();
    $isLocalhost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

    if (!$isLocalhost) {
        abort(404);
    }

    $assetPath = public_path('customer-app/assets/' . $path);
    if (!file_exists($assetPath)) {
        abort(404);
    }

    $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'js' => 'application/javascript',
        'css' => 'text/css; charset=UTF-8',
        'map' => 'application/json',
        'json' => 'application/json',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
    ];
    $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

    return response(file_get_contents($assetPath), 200, ['Content-Type' => $contentType]);
})->where('path', '.*');

// Alias /app/* to /login so SPA routes match base '/'
Route::get('/app/{any?}', function () {
    return redirect('/login');
})->where('any', '.*');

// Serve SPA at root for local parity with app.consultoriawk.com
// Note: This must NOT capture /api/* routes - those are handled by api.php
Route::get('/{any?}', function () use ($renderCustomerApp) {
    return $renderCustomerApp();
})->where('any', '^(?!api).*$');