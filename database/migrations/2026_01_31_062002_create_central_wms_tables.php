<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 2. WMS (Warehouses & Inventory)
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 12, 3)->default(0); // On Hand
            $table->decimal('reserve_quantity', 12, 3)->default(0); // Committed
            $table->timestamps();

            $table->unique(['warehouse_id', 'product_id']);
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('inventory_stocks')->cascadeOnDelete();
            $table->string('type'); // purchase, sale, transfer, adjustment, return
            $table->decimal('quantity', 12, 3); // Negative for decrease, Positive for increase
            $table->unsignedBigInteger('reference_id')->nullable(); // Order ID, PO ID
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Who did it
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_stocks');
        Schema::dropIfExists('warehouses');
    }
};
