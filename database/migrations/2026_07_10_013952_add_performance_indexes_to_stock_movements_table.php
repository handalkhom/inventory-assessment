<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $indexes = Schema::getIndexes('stock_movements');
            $indexNames = array_column($indexes, 'name');
            
            if (!in_array('idx_warehouse_created', $indexNames)) {
                $table->index(['warehouse_id', 'created_at'], 'idx_warehouse_created');
            }
            if (!in_array('idx_product_movement', $indexNames)) {
                $table->index(['product_id', 'movement_type'], 'idx_product_movement');
            }
            if (!in_array('idx_reference_number', $indexNames)) {
                $table->index('reference_number', 'idx_reference_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_warehouse_created');
            $table->dropIndex('idx_product_movement');
            $table->dropIndex('idx_reference_number');
        });
    }
};
