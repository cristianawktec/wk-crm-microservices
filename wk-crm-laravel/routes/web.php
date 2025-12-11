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

Route::get('/admin', function () {
    return view('admin');
});

// Vue SPA Customer App fallback route
Route::get('/customer-app/{any?}', function () {
    return file_get_contents(public_path('customer-app/index.html'));
})->where('any', '.*');