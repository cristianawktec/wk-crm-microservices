<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds - Using raw SQL to avoid Spatie UUID issues
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('permissions')->delete();
        DB::table('roles')->delete();

        // Permissions list
        $permissions = [
            'view_dashboard', 'export_dashboard',
            'create_customers', 'read_customers', 'update_customers', 'delete_customers', 'export_customers',
            'create_leads', 'read_leads', 'update_leads', 'delete_leads', 'export_leads',
            'create_opportunities', 'read_opportunities', 'update_opportunities', 'delete_opportunities', 'export_opportunities',
            'manage_sellers', 'view_sellers_performance', 'read_sellers',
            'manage_users', 'manage_roles', 'manage_permissions',
            'view_system_logs', 'manage_system_settings',
        ];

        // Insert permissions
        $permissionsMap = [];
        foreach ($permissions as $permission) {
            $id = (string) Str::uuid();
            DB::table('permissions')->insert([
                'id' => $id,
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $permissionsMap[$permission] = $id;
        }

        // Insert roles
        $adminRoleId = (string) Str::uuid();
        $sellerRoleId = (string) Str::uuid();
        $customerRoleId = (string) Str::uuid();

        DB::table('roles')->insert([
            ['id' => $adminRoleId, 'name' => 'admin', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $sellerRoleId, 'name' => 'seller', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $customerRoleId, 'name' => 'customer', 'guard_name' => 'web', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Assign all permissions to admin
        foreach ($permissionsMap as $permissionId) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
            ]);
        }

        // Assign seller permissions
        $sellerPermissions = ['view_dashboard', 'create_leads', 'read_leads', 'update_leads', 'create_opportunities', 'read_opportunities', 'update_opportunities', 'read_customers', 'read_sellers', 'export_leads', 'export_opportunities'];
        foreach ($sellerPermissions as $permission) {
            if (isset($permissionsMap[$permission])) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionsMap[$permission],
                    'role_id' => $sellerRoleId,
                ]);
            }
        }

        // Assign customer permissions
        $customerPermissions = ['view_dashboard', 'read_customers', 'read_leads', 'read_opportunities'];
        foreach ($customerPermissions as $permission) {
            if (isset($permissionsMap[$permission])) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionsMap[$permission],
                    'role_id' => $customerRoleId,
                ]);
            }
        }
    }
}
