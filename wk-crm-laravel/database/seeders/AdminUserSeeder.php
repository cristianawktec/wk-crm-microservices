<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@consultoriawk.com'],
            [
                'name' => 'Admin WK',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@123456')),
            ]
        );
        $admin->assignRole('admin');

        // Create test seller user
        $seller = User::updateOrCreate(
            ['email' => 'seller@consultoriawk.com'],
            [
                'name' => 'Seller WK',
                'password' => Hash::make(env('SELLER_PASSWORD', 'Seller@123456')),
            ]
        );
        $seller->assignRole('seller');

        // Create test customer user
        $customer = User::updateOrCreate(
            ['email' => 'customer@consultoriawk.com'],
            [
                'name' => 'Customer WK',
                'password' => Hash::make(env('CUSTOMER_PASSWORD', 'Customer@123456')),
            ]
        );
        $customer->assignRole('customer');
    }
}
