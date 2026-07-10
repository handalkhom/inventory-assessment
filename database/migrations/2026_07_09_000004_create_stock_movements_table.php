<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('warehouse_id')->constrained();
            $table->enum('movement_type', ['in', 'out', 'transfer', 'adjustment']);
            $table->integer('quantity'); // positive for in, negative for out
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('moved_by', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
