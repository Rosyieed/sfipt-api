<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $units = [
                [
                    'code' => 'PCS',
                    'name' => 'Pcs',
                    'description' => 'Satuan per item',
                    'is_active' => true,
                ],
                [
                    'code' => 'KG',
                    'name' => 'Kilogram',
                    'description' => 'Satuan berat kilogram',
                    'is_active' => true,
                ],
                [
                    'code' => 'GR',
                    'name' => 'Gram',
                    'description' => 'Satuan berat gram',
                    'is_active' => true,
                ],
                [
                    'code' => 'M',
                    'name' => 'Meter',
                    'description' => 'Satuan panjang meter',
                    'is_active' => true,
                ],
                [
                    'code' => 'CM',
                    'name' => 'Centimeter',
                    'description' => 'Satuan panjang centimeter',
                    'is_active' => true,
                ],
                [
                    'code' => 'MM',
                    'name' => 'Millimeter',
                    'description' => 'Satuan panjang millimeter',
                    'is_active' => true,
                ],
                [
                    'code' => 'L',
                    'name' => 'Liter',
                    'description' => 'Satuan volume liter',
                    'is_active' => true,
                ],
                [
                    'code' => 'ML',
                    'name' => 'Milliliter',
                    'description' => 'Satuan volume milliliter',
                    'is_active' => true,
                ],
                [
                    'code' => 'BOX',
                    'name' => 'Box',
                    'description' => 'Satuan per kotak',
                    'is_active' => true,
                ],
                [
                    'code' => 'PACK',
                    'name' => 'Pack',
                    'description' => 'Satuan per kemasan',
                    'is_active' => true,
                ],
                [
                    'code' => 'SET',
                    'name' => 'Set',
                    'description' => 'Satuan per set',
                    'is_active' => true,
                ],
                [
                    'code' => 'ROLL',
                    'name' => 'Roll',
                    'description' => 'Satuan per gulungan',
                    'is_active' => true,
                ],
                [
                    'code' => 'LBR',
                    'name' => 'Lembar',
                    'description' => 'Satuan per lembar',
                    'is_active' => true,
                ],
            ];

            foreach ($units as $unit) {
                Unit::firstOrCreate(
                    ['code' => $unit['code']],
                    [
                        'name' => $unit['name'],
                        'description' => $unit['description'],
                        'is_active' => $unit['is_active'],
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }
}
