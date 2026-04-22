<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            $guardName = array_key_exists('sanctum', config('auth.guards', []))
                ? 'sanctum'
                : config('auth.defaults.guard');

            $permissions = [
                'admin.access',
                'app.access',

                'users.view',
                'users.create',
                'users.update',
                'users.delete',

                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',

                'permissions.view',
                'permissions.create',
                'permissions.delete',
            ];

            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => $guardName,
                ]);
            }

            $adminRole = Role::firstOrCreate([
                'name' => 'admin',
                'guard_name' => $guardName,
            ]);

            $warehouseOperatorRole = Role::firstOrCreate([
                'name' => 'warehouse_operator',
                'guard_name' => $guardName,
            ]);

            $productionManagerRole = Role::firstOrCreate([
                'name' => 'production_manager',
                'guard_name' => $guardName,
            ]);

            $adminRole->syncPermissions(Permission::query()->where('guard_name', $guardName)->pluck('name')->all());
            $warehouseOperatorRole->syncPermissions(['app.access']);
            $productionManagerRole->syncPermissions(['app.access']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
