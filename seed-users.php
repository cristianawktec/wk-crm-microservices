<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@consultoriawk.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@consultoriawk.com'],
            [
                'name' => 'Cliente Teste',
                'password' => bcrypt('customer123'),
            ]
        );
    }
}
