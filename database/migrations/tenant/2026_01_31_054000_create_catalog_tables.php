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
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('image')->nullable();

            $table->text('description')->nullable(); // New
            $table->string('banner_image')->nullable(); // New
            $table->integer('sort_order')->default(0); // New
            $table->boolean('is_featured')->default(false); // New
            $table->boolean('is_menu')->default(false); // New: Show in main menu

            $table->boolean('is_active')->default(true);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Brands (Enterprise Grade)
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->string('banner_image')->nullable(); // New: For brand pages
            $table->string('website')->nullable(); // New: External link
            $table->string('country_origin')->nullable(); // New: "Made in X"

            $table->text('description')->nullable(); // New: Rich text

            $table->integer('sort_order')->default(0); // New: Display order
            $table->boolean('is_featured')->default(false); // New: For homepage/sliders
            $table->boolean('is_active')->default(true);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Products (Rich Agri Schema)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable(); // Nullable for parent variable products
            $table->string('barcode')->nullable();
            $table->string('type')->default('simple'); // simple, variable
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();

            // Agriculture Specifics
            $table->date('harvest_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Agri Fields
            $table->string('technical_name')->nullable(); // e.g. Imidacloprid 17.8% SL
            $table->string('application_method')->nullable(); // e.g. Foliar Spray
            $table->text('usage_instructions')->nullable();
            $table->json('target_crops')->nullable();
            $table->json('target_pests')->nullable();
            $table->string('pre_harvest_interval')->nullable();
            $table->string('shelf_life')->nullable(); // e.g. 24 Months
            $table->string('certificate_url')->nullable();

            $table->string('origin')->nullable(); // Farm Location/Region
            $table->boolean('is_organic')->default(false);
            $table->string('certification_number')->nullable(); // Organic/GAP Cert
            $table->string('unit_type')->default('kg'); // kg, ton, quintal, crate, bundle

            // Pricing & Specs
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('mrp', 12, 2)->nullable(); // Max Retail Price
            $table->decimal('tax_rate', 5, 2)->nullable(); // Specific tax rate
            $table->string('hsn_code')->nullable(); // GST HSN

            $table->string('default_discount_type')->nullable()->default('fixed');
            $table->decimal('default_discount_value', 15, 2)->nullable()->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);

            $table->decimal('weight', 8, 3)->nullable(); // kg
            $table->json('dimensions')->nullable(); // L,W,H
            $table->string('packing_size')->nullable(); // e.g. "500 ml"

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('manage_stock')->default(true);
            $table->decimal('stock_on_hand', 12, 3)->default(0);
            $table->integer('min_order_qty')->default(1);
            $table->integer('reorder_level')->default(0);

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
    }
};
