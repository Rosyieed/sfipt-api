<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            Category::firstOrCreate(
                ['code' => 'KAYU'],
                [
                    'name' => 'Kayu',
                    'description' => 'Material berbahan kayu',
                    'is_active' => true,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
