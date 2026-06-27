<?php

namespace Database\Seeders;

use App\Models\Bom;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BomSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $category = Category::query()->where('code', 'KAYU')->first();
            if (!$category) {
                $category = Category::create([
                    'code' => 'KAYU',
                    'name' => 'Kayu',
                    'description' => 'Material berbahan kayu',
                    'is_active' => true,
                ]);
            }

            $pcs = Unit::query()->where('code', 'PCS')->first();
            $box = Unit::query()->where('code', 'BOX')->first();
            $l = Unit::query()->where('code', 'L')->first();

            // 1. Seed raw materials if not exist
            $plank = Product::firstOrCreate(
                ['sku' => 'KAYU-RAW-001'],
                [
                    'barcode' => '899000000001',
                    'name' => 'Papan Kayu Pinus (Plank)',
                    'category_id' => $category->id,
                    'unit_id' => $pcs->id,
                    'type' => 'raw_material',
                    'min_stock' => 10,
                    'description' => 'Bahan baku papan kayu ukuran standar.',
                    'is_active' => true,
                ]
            );

            $leg = Product::firstOrCreate(
                ['sku' => 'KAYU-RAW-002'],
                [
                    'barcode' => '899000000002',
                    'name' => 'Kaki Meja Kayu (Leg)',
                    'category_id' => $category->id,
                    'unit_id' => $pcs->id,
                    'type' => 'raw_material',
                    'min_stock' => 20,
                    'description' => 'Kaki meja kayu silinder.',
                    'is_active' => true,
                ]
            );

            $screw = Product::firstOrCreate(
                ['sku' => 'PAKU-RAW-001'],
                [
                    'barcode' => '899000000003',
                    'name' => 'Sekrup Kayu 5cm',
                    'category_id' => $category->id,
                    'unit_id' => $pcs->id,
                    'type' => 'raw_material',
                    'min_stock' => 100,
                    'description' => 'Sekrup stainless steel untuk kayu.',
                    'is_active' => true,
                ]
            );

            $glue = Product::firstOrCreate(
                ['sku' => 'LEM-RAW-001'],
                [
                    'barcode' => '899000000004',
                    'name' => 'Lem Kayu Prima',
                    'category_id' => $category->id,
                    'unit_id' => $l?->id ?? $pcs->id,
                    'type' => 'raw_material',
                    'min_stock' => 5,
                    'description' => 'Lem kayu cair kuat kualitas tinggi.',
                    'is_active' => true,
                ]
            );

            // 2. Seed finished goods if not exist
            $table = Product::firstOrCreate(
                ['sku' => 'MEJA-FG-001'],
                [
                    'barcode' => '899000000101',
                    'name' => 'Meja Makan Minimalis',
                    'category_id' => $category->id,
                    'unit_id' => $pcs->id,
                    'type' => 'finished_good',
                    'min_stock' => 2,
                    'description' => 'Meja makan kayu pinus minimalis 4 kursi.',
                    'is_active' => true,
                ]
            );

            $chair = Product::firstOrCreate(
                ['sku' => 'KURSI-FG-001'],
                [
                    'barcode' => '899000000102',
                    'name' => 'Kursi Kayu Standard',
                    'category_id' => $category->id,
                    'unit_id' => $pcs->id,
                    'type' => 'finished_good',
                    'min_stock' => 8,
                    'description' => 'Kursi kayu pinus standar sandaran punggung.',
                    'is_active' => true,
                ]
            );

            // 3. Seed BOM for Meja Makan
            $bomTable = Bom::firstOrCreate(
                ['code' => 'BOM-MEJA-001'],
                [
                    'product_id' => $table->id,
                    'name' => 'Standar Pembuatan Meja Makan',
                    'description' => 'Resep standar pembuatan 1 unit meja makan minimalis.',
                    'output_qty' => 1.0000,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $bomTable->items()->delete();
            $bomTable->items()->createMany([
                [
                    'material_id' => $plank->id,
                    'qty_needed' => 4.0000,
                    'unit_id' => $plank->unit_id,
                    'notes' => 'Papan bagian atas meja',
                ],
                [
                    'material_id' => $leg->id,
                    'qty_needed' => 4.0000,
                    'unit_id' => $leg->unit_id,
                    'notes' => 'Penyangga meja',
                ],
                [
                    'material_id' => $screw->id,
                    'qty_needed' => 24.0000,
                    'unit_id' => $screw->unit_id,
                    'notes' => 'Sekrup penguat struktur',
                ],
                [
                    'material_id' => $glue->id,
                    'qty_needed' => 1.0000,
                    'unit_id' => $glue->unit_id,
                    'notes' => 'Lem perekat sambungan',
                ]
            ]);

            // 4. Seed BOM for Kursi Kayu
            $bomChair = Bom::firstOrCreate(
                ['code' => 'BOM-KURSI-001'],
                [
                    'product_id' => $chair->id,
                    'name' => 'Standar Pembuatan Kursi Kayu',
                    'description' => 'Resep standar pembuatan 1 unit kursi kayu standard.',
                    'output_qty' => 1.0000,
                    'is_default' => true,
                    'is_active' => true,
                ]
            );

            $bomChair->items()->delete();
            $bomChair->items()->createMany([
                [
                    'material_id' => $plank->id,
                    'qty_needed' => 2.0000,
                    'unit_id' => $plank->unit_id,
                    'notes' => 'Papan sandaran dan alas duduk',
                ],
                [
                    'material_id' => $leg->id,
                    'qty_needed' => 4.0000,
                    'unit_id' => $leg->unit_id,
                    'notes' => 'Kaki kursi',
                ],
                [
                    'material_id' => $screw->id,
                    'qty_needed' => 12.0000,
                    'unit_id' => $screw->unit_id,
                    'notes' => 'Sekrup penguat',
                ],
                [
                    'material_id' => $glue->id,
                    'qty_needed' => 0.5000,
                    'unit_id' => $glue->unit_id,
                    'notes' => 'Lem secukupnya',
                ]
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
