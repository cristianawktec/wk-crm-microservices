<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing users
        User::whereIn('email', [
            'admin@consultoriawk.com',
            'seller@consultoriawk.com',
            'customer@consultoriawk.com',
        ])->delete();

        // Get role IDs
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $sellerRole = Role::where('name', 'seller')->firstOrFail();
        $customerRole = Role::where('name', 'customer')->firstOrFail();

        $now = now();

        // Create admin user
        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'name' => 'Admin WK',
            'email' => 'admin@consultoriawk.com',
            'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@123456')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => $adminRole->id,
            'model_type' => User::class,
            'model_id' => $adminId,
        ]);

        // Create seller user
        $sellerId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $sellerId,
            'name' => 'Seller WK',
            'email' => 'seller@consultoriawk.com',
            'password' => Hash::make(env('SELLER_PASSWORD', 'Seller@123456')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => $sellerRole->id,
            'model_type' => User::class,
            'model_id' => $sellerId,
        ]);

        // Create customer user
        $customerId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $customerId,
            'name' => 'Customer WK',
            'email' => 'customer@consultoriawk.com',
            'password' => Hash::make(env('CUSTOMER_PASSWORD', 'Customer@123456')),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('model_has_roles')->insert([
            'role_id' => $customerRole->id,
            'model_type' => User::class,
            'model_id' => $customerId,
        ]);
    }
}
