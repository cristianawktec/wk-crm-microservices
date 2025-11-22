<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('leads')->insert([
            [
                'nome' => 'Carlos Alberto',
                'email' => 'carlos.alberto@example.com',
                'telefone' => '(11) 97777-8888',
                'empresa' => 'Tech Solutions',
                'origem' => 'Website',
                'status' => 'novo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Fernanda Lima',
                'email' => 'fernanda.lima@example.com',
                'telefone' => '(21) 96666-7777',
                'empresa' => 'Digital Marketing',
                'origem' => 'Indicação',
                'status' => 'contato',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Roberto Silva',
                'email' => 'roberto@example.com',
                'telefone' => '(11) 95555-4444',
                'empresa' => 'Startup XYZ',
                'origem' => 'LinkedIn',
                'status' => 'qualificado',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
