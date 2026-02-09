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
        // 0. Tax System
        Schema::create('tax_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Standard Rate, Reduced Rate
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_class_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // GST 18%
            $table->decimal('rate', 8, 2); // 18.00
            $table->string('zone')->nullable(); // For future use
            $table->json('breakdown')->nullable(); // {cgst: 9, sgst: 9}
            $table->timestamps();
        });

        // 1. Catalog
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_menu')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('country_of_origin')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable(); // Made nullable for flexibility
            $table->string('barcode')->nullable();
            $table->string('type')->default('simple'); // simple, variable, bundle

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();

            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->nullable(); // Max Retail Price
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->foreignId('tax_class_id')->nullable()->constrained('tax_classes')->nullOnDelete();
            $table->decimal('tax_rate', 5, 2)->nullable(); // Specific tax rate
            $table->string('hsn_code')->nullable(); // GST HSN

            $table->string('default_discount_type')->nullable()->default('fixed');
            $table->decimal('default_discount_value', 15, 2)->nullable()->default(0);

            // Inventory
            $table->boolean('manage_stock')->default(true);
            $table->decimal('stock_on_hand', 12, 3)->default(0);
            $table->integer('min_order_qty')->default(1);
            $table->integer('reorder_level')->default(0);
            $table->string('unit_type')->default('piece'); // kg, gm, ltr, piece
            $table->string('packing_size')->nullable(); // e.g. "500 ml"

            // Tax & Status
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Agriculture Specifics
            $table->date('harvest_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->string('technical_name')->nullable(); // For chemicals
            $table->string('application_method')->nullable(); // Spray, Drip
            $table->text('usage_instructions')->nullable();
            $table->json('target_crops')->nullable(); // Wheat, Rice
            $table->json('target_pests')->nullable(); // Aphids, Bollworm
            $table->string('pre_harvest_interval')->nullable();
            $table->string('shelf_life')->nullable();
            $table->string('origin')->nullable();
            $table->boolean('is_organic')->default(false);
            $table->string('certification_number')->nullable();
            $table->string('certificate_url')->nullable();

            // WMS attributes
            $table->decimal('weight', 8, 3)->nullable(); // kg
            $table->json('dimensions')->nullable(); // L x W x H

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });

        // Variants
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Size, Color
            $table->timestamps();
        });

        Schema::create('product_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // Small, Red
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2)->nullable(); // Override parent
            $table->decimal('stock_quantity', 12, 3)->default(0);
            $table->timestamps();
        });

        // Variant Combinations (Link Variant -> Option Values)
        Schema::create('product_variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_option_value_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_values');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_option_values');
        Schema::dropIfExists('product_options');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('tax_classes');
    }
};
