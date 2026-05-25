<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            if (! Schema::hasColumn('warehouses', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('id');
            }
        });

        DB::table('warehouses')
            ->whereNull('code')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $warehouse): void {
                DB::table('warehouses')
                    ->where('id', $warehouse->id)
                    ->update(['code' => 'WH-'.str_pad((string) $warehouse->id, 3, '0', STR_PAD_LEFT)]);
            });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE warehouses MODIFY location VARCHAR(255) NULL');
            DB::statement("ALTER TABLE warehouses MODIFY type ENUM('raw', 'wip', 'finished', 'general') NOT NULL DEFAULT 'raw'");
            DB::statement('ALTER TABLE warehouses MODIFY code VARCHAR(50) NOT NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE warehouses MODIFY type ENUM('raw', 'wip', 'finished') NOT NULL DEFAULT 'raw'");
            DB::statement('ALTER TABLE warehouses MODIFY location VARCHAR(255) NOT NULL');
        }

        Schema::table('warehouses', function (Blueprint $table) {
            if (Schema::hasColumn('warehouses', 'code')) {
                $table->dropUnique(['code']);
                $table->dropColumn('code');
            }
        });
    }
};
