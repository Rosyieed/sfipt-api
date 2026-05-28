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

            $guardName = 'sanctum';

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

                'warehouses.view',
                'warehouses.create',
                'warehouses.update',
                'warehouses.delete',

                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',

                'units.view',
                'units.create',
                'units.update',
                'units.delete',

                'products.view',
                'products.create',
                'products.update',
                'products.delete',

                'stocks.view',

                'mutations.view',
                'mutations.create',

                'boms.view',
                'boms.create',
                'boms.update',
                'boms.delete',

                'production-orders.view',
                'production-orders.create',
                'production-orders.update',
                'production-orders.execute',
                'production-orders.cancel',

                'dashboard.view',
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

            $adminRole->syncPermissions(
                Permission::query()
                    ->where('guard_name', $guardName)
                    ->pluck('name')
                    ->all()
            );
            $warehouseOperatorRole->syncPermissions([
                'app.access',
                'warehouses.view',
                'warehouses.create',
                'warehouses.update',
                'warehouses.delete',
                'categories.view',
                'categories.create',
                'categories.update',
                'categories.delete',
                'units.view',
                'units.create',
                'units.update',
                'units.delete',
                'products.view',
                'products.create',
                'products.update',
                'products.delete',
                'stocks.view',
                'mutations.view',
                'mutations.create',
            ]);
            $productionManagerRole->syncPermissions([
                'app.access',
                'warehouses.view',
                'categories.view',
                'units.view',
                'products.view',
                'stocks.view',
                'mutations.view',
                'boms.view',
                'boms.create',
                'boms.update',
                'production-orders.view',
                'production-orders.create',
                'production-orders.update',
                'production-orders.execute',
                'production-orders.cancel',
                'dashboard.view',
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
