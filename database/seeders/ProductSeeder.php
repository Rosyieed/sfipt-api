<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::query()->where('code', 'KAYU')->first();
            $unit = Unit::query()->where('code', 'PCS')->first();

            if ($category && $unit) {
                Product::firstOrCreate(
                    ['sku' => 'KAYU-RAW-001'],
                    [
                        'barcode' => '899000000001',
                        'name' => 'Kayu Raw Material',
                        'category_id' => $category->id,
                        'unit_id' => $unit->id,
                        'type' => 'raw_material',
                        'min_stock' => 10,
                        'description' => 'Contoh bahan baku kayu untuk kebutuhan development.',
                        'is_active' => true,
                    ],
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
