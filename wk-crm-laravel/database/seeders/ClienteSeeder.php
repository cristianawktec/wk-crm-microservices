<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('clientes')->insert([
            [
                'nome' => 'João Silva',
                'email' => 'joao@example.com',
                'telefone' => '(11) 98765-4321',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Maria Santos',
                'email' => 'maria@example.com',
                'telefone' => '(11) 91234-5678',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Pedro Oliveira',
                'email' => 'pedro@example.com',
                'telefone' => '(21) 99876-5432',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Ana Costa',
                'email' => 'ana@example.com',
                'telefone' => '(11) 95555-1234',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nome' => 'Carlos Souza',
                'email' => 'carlos@example.com',
                'telefone' => '(21) 94444-5678',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
