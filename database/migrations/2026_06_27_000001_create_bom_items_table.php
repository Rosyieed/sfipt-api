<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('products')->restrictOnDelete();
            $table->decimal('qty_needed', 18, 4);
            $table->foreignId('unit_id')->nullable()->constrained('units')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('bom_id');
            $table->index('material_id');
            $table->index('unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
