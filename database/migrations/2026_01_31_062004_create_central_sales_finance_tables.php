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
        // 0. Expenses (Central)
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('category')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 1. Finance Basics
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Wholesale, Retail
            $table->string('currency')->default('USD');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // 2. Orders (Moved Up for FK dependencies)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('warehouse_id')->nullable()->constrained(); // Allocation
            $table->string('status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
            $table->string('verification_status')->default('unverified');
            $table->string('payment_status')->default('unpaid'); // unpaid, partial, paid, refunded
            $table->string('shipping_status')->default('pending'); // pending, shipped, delivered

            // Financials
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_type')->default('fixed'); // fixed, percent
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);

            // Methods & Tracking
            $table->string('payment_method')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('discount_code')->nullable();

            // Addresses (Snapshots)
            $table->unsignedBigInteger('billing_address_id')->nullable();
            $table->unsignedBigInteger('shipping_address_id')->nullable();

            $table->text('notes')->nullable();
            $table->timestamp('placed_at')->useCurrent();
            $table->timestamp('scheduled_at')->nullable();
            $table->boolean('is_future_order')->default(false);
            $table->json('tags')->nullable();
            $table->string('currency', 10)->default('INR');
            $table->string('channel', 20)->default('web'); // web, app, pos

            // Lifecycle Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->string('sku');
            $table->string('product_name'); // Snapshot
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 12, 2);
            $table->string('discount_type')->default('fixed'); // fixed, percent
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable(); // Snapshot of cost
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // billing, shipping
            $table->string('name');
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country');
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->date('sale_start')->nullable();
            $table->date('sale_end')->nullable();
            $table->integer('min_quantity')->default(1); // For Tiered Pricing
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('description')->nullable();
            $table->string('type')->default('fixed'); // fixed, percent
            $table->decimal('amount', 12, 2);
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('order_id')->constrained();
            $table->timestamp('used_at')->useCurrent();
        });

        // 3. Logistics (Shipments)
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('warehouse_id')->constrained(); // Fulfilling warehouse
            $table->string('tracking_number')->nullable();
            $table->string('carrier')->nullable(); // FedEx, UPS
            $table->string('status')->default('shipped'); // shipped, in_transit, delivered
            $table->string('shipping_label_url')->nullable();
            $table->decimal('weight', 8, 3)->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->unsignedInteger('packages_count')->default(1);
            $table->string('dimensions', 50)->nullable(); // LxWxH
            $table->timestamps();
        });

        // 4. Billing (Invoices & Payments)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('order_id')->constrained();
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('draft'); // draft, sent, paid, overdue, void
            $table->string('gstin', 20)->nullable();
            $table->string('pdf_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()->constrained();
            $table->foreignId('order_id')->constrained();
            $table->string('method'); // cash, card, bank
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->string('gateway', 30)->nullable(); // stripe, razorpay
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });

        // 5. Returns (RMA)
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('rma_number')->unique();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->string('status')->default('requested'); // requested, approved, received, refunded, rejected
            $table->text('reason')->nullable();
            $table->string('refund_method')->nullable(); // credit, refund
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inspected_at')->nullable();
            $table->decimal('refunded_amount', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->decimal('quantity', 12, 3);
            $table->decimal('quantity_received', 12, 3)->nullable();
            $table->string('condition')->default('sellable'); // sellable, damaged
            $table->string('condition_received')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('expenses');
    }
};
