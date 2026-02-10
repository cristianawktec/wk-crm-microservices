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

// Vue SPA Customer App fallback route
Route::get('/customer-app/{any?}', function () {
    return file_get_contents(public_path('customer-app/index.html'));
})->where('any', '.*');

// Alias /app/* to /login so SPA routes match base '/'
Route::get('/app/{any?}', function () {
    return redirect('/login');
})->where('any', '.*');

// Serve SPA at root for local parity with app.consultoriawk.com
Route::get('/{any?}', function () {
    return file_get_contents(public_path('customer-app/index.html'));
})->where('any', '^(?!api|admin|customer-app|docs|deploy\.php|up).*$');