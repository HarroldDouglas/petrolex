<?php

// database/seeders/RolePermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->resetTables();
        $config = config('permissions_structure');

        $this->createPermissions($config['permissions']);
        $this->createRolesWithPermissions($config['roles']);

        $this->command->info('Roles and permissions created successfully!');
    }

    /**
     * Reset permission and role tables.
     */
    private function resetTables(): void
    {
        $this->command->info('Resetting permission and role tables...');

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Truncate all related tables to avoid duplicates
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->command->info('Tables reset successfully.');
    }

    /**
     * Create all permissions from config.
     *
     * @param  array  $permissions  List of permission names
     */
    private function createPermissions(array $permissions): void
    {
        $this->command->info('Creating permissions...');

        $count = 0;
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $count++;
        }

        $this->command->info("{$count} permissions created.");
    }

    /**
     * Create roles and assign permissions to them.
     *
     * @param  array  $roles  Roles with their respective permissions
     */
    private function createRolesWithPermissions(array $roles): void
    {
        $this->command->info('Creating roles and assigning permissions...');

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            if (! empty($permissions)) {
                $role->syncPermissions($permissions);
                $this->command->info("Role '{$roleName}' created with ".count($permissions).' permissions.');
            } else {
                $this->command->info("Role '{$roleName}' created without specific permissions.");
            }
        }
    }
}
