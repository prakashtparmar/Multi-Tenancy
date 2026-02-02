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
        // 1. Enhance Orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'tags')) {
                $table->json('tags')->nullable()->after('status');
            }
            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency')->default('INR')->after('grand_total');
            }
            if (!Schema::hasColumn('orders', 'channel')) {
                $table->string('channel')->default('web')->after('order_number'); // web, app, pos
            }
        });

        // 2. Enhance Shipments
        Schema::table('shipments', function (Blueprint $table) {
            if (!Schema::hasColumn('shipments', 'estimated_delivery_date')) {
                $table->date('estimated_delivery_date')->nullable()->after('shipped_at');
            }
            if (!Schema::hasColumn('shipments', 'packages_count')) {
                $table->integer('packages_count')->default(1)->after('weight');
            }
            if (!Schema::hasColumn('shipments', 'dimensions')) {
                 $table->string('dimensions')->nullable()->after('weight'); // LxWxH
            }
        });

        // 3. Enhance Invoices
        Schema::table('invoices', function (Blueprint $table) {
             if (!Schema::hasColumn('invoices', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable()->after('pdf_path');
            }
            if (!Schema::hasColumn('invoices', 'gstin')) {
                $table->string('gstin')->nullable()->after('invoice_number');
            }
        });

        // 4. Enhance Payments
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway')->nullable()->after('method'); // stripe, razorpay
            }
            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_response']);
        });

        Schema::table('invoices', function (Blueprint $table) {
             $table->dropColumn(['pdf_path', 'notes', 'gstin']);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_date', 'packages_count', 'dimensions']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['tags', 'currency', 'channel']);
        });
    }
};
