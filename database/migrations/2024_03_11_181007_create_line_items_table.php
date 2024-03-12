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
        Schema::create('line_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('variation_id');
            $table->unsignedInteger('quantity');
            $table->string('tax_class')->nullable();
            $table->float('subtotal');
            $table->float('subtotal_tax');
            $table->float('total');
            $table->float('total_tax');
            $table->json('meta_data');
            $table->json('taxes');
            $table->string('sku')->nullable();
            $table->float('price');
            $table->foreignIdFor(\App\Models\Order::class)->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('line_items');
    }
};
