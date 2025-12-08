<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Dashboard
            'view_dashboard',
            'export_dashboard',

            // Customers
            'create_customers',
            'read_customers',
            'update_customers',
            'delete_customers',
            'export_customers',

            // Leads
            'create_leads',
            'read_leads',
            'update_leads',
            'delete_leads',
            'export_leads',

            // Opportunities
            'create_opportunities',
            'read_opportunities',
            'update_opportunities',
            'delete_opportunities',
            'export_opportunities',

            // Sellers (Admin only)
            'manage_sellers',
            'view_sellers_performance',

            // Users & Access Control
            'manage_users',
            'manage_roles',
            'manage_permissions',

            // System
            'view_system_logs',
            'manage_system_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // Create Roles
        $adminRole = Role::updateOrCreate(['name' => 'admin']);
        $sellerRole = Role::updateOrCreate(['name' => 'seller']);
        $customerRole = Role::updateOrCreate(['name' => 'customer']);

        // Assign all permissions to Admin
        $adminRole->syncPermissions(Permission::all());

        // Assign Seller permissions
        $sellerPermissions = [
            'view_dashboard',
            'create_leads',
            'read_leads',
            'update_leads',
            'create_opportunities',
            'read_opportunities',
            'update_opportunities',
            'read_customers',
            'export_leads',
            'export_opportunities',
        ];
        $sellerRole->syncPermissions($sellerPermissions);

        // Assign Customer permissions
        $customerPermissions = [
            'view_dashboard',
            'read_customers',
            'read_leads',
            'read_opportunities',
        ];
        $customerRole->syncPermissions($customerPermissions);
    }
}
