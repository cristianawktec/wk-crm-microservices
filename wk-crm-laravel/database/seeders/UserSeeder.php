<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Usuário administrador padrão
        User::create([
            'name' => 'Administrador WK CRM',
            'email' => 'admin@wkcrm.com',
            'password' => Hash::make('password'),
        ]);

        // Usuário de teste
        User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
        ]);

        echo "✅ Usuários criados com sucesso!\n";
        echo "📧 admin@wkcrm.com / password\n";
        echo "📧 joao@example.com / password123\n";
    }
}
