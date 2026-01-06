<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Opportunity;
use Carbon\Carbon;

class TestOpportunitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Opportunity::create([
            'title' => 'Implementação SaaS - Tech Corp',
            'description' => 'Venda de solução SaaS para empresa de tecnologia',
            'value' => 15000,
            'status' => 'won',
        ]);

        Opportunity::create([
            'title' => 'Consultoria em Digital - Finance Bank',
            'description' => 'Consultoria em transformação digital',
            'value' => 25000,
            'status' => 'won',
        ]);

        Opportunity::create([
            'title' => 'Plataforma de Análise - Retail Co',
            'description' => 'Desenvolvimento de plataforma de análise de dados',
            'value' => 30000,
            'status' => 'open',
        ]);

        Opportunity::create([
            'title' => 'Integração API - Logistics Plus',
            'description' => 'Integração de APIs com sistema logístico',
            'value' => 8000,
            'status' => 'lost',
        ]);

        Opportunity::create([
            'title' => 'Cloud Migration - Healthcare Inc',
            'description' => 'Migração para nuvem e infraestrutura',
            'value' => 50000,
            'status' => 'open',
        ]);

        Opportunity::create([
            'title' => 'Mobile App - E-commerce Ltd',
            'description' => 'Desenvolvimento de aplicativo mobile',
            'value' => 20000,
            'status' => 'won',
        ]);
    }
}
