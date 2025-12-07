<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'admin@wkcrm.com';
        $password = 'Admin@12345';

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin WK CRM',
                'password' => Hash::make($password),
            ]
        );
    }
}
