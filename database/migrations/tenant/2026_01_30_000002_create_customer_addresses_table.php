<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Type & control
            $table->enum('type', ['billing', 'shipping', 'both'])->default('shipping');
            $table->string('label')->nullable(); // Home, Office, Farm, Warehouse

            // Contact
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 20)->nullable();

            // Address
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('village')->nullable();
            $table->string('taluka')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable()->index();
            $table->string('country')->default('India');
            $table->string('pincode', 10)->nullable();
            $table->string('post_office')->nullable();

            // Geo
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'type']);
            $table->index(['state', 'district']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
