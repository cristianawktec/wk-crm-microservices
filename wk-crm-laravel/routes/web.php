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

// Página de CRUD de Clientes dentro do dashboard AdminLTE
Route::get('/admin/clientes', function () {
    return view('clientes');
});

// Página de CRUD de Leads
Route::get('/admin/leads', function () {
    return view('leads');
});