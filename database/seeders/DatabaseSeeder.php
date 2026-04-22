<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        $adminEmail = env('DEFAULT_ADMIN_EMAIL', 'admin@gmail.com');
        $adminPassword = env('DEFAULT_ADMIN_PASSWORD', 'password');

        DB::beginTransaction();

        try {
            $admin = User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => $adminPassword,
                ]
            );

            $admin->syncRoles(['admin']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
