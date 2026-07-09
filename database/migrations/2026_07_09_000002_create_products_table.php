<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('weight_kg', 10, 2);
            $table->enum('category', ['raw_material', 'finished_goods', 'packaging', 'spare_part']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
