<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /* ------------------------------------------------------------------ */
        /* Orders                                                             */
        /* ------------------------------------------------------------------ */
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'tags')) {
                $table->json('tags')->nullable();
            }

            if (!Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 10)->default('INR');
            }

            if (!Schema::hasColumn('orders', 'channel')) {
                $table->string('channel', 20)->default('web'); // web, app, pos
            }
        });

        /* ------------------------------------------------------------------ */
        /* Shipments                                                          */
        /* ------------------------------------------------------------------ */
        Schema::table('shipments', function (Blueprint $table) {

            if (!Schema::hasColumn('shipments', 'estimated_delivery_date')) {
                $table->date('estimated_delivery_date')->nullable();
            }

            if (!Schema::hasColumn('shipments', 'packages_count')) {
                $table->unsignedInteger('packages_count')->default(1);
            }

            if (!Schema::hasColumn('shipments', 'dimensions')) {
                $table->string('dimensions', 50)->nullable(); // LxWxH
            }
        });

        /* ------------------------------------------------------------------ */
        /* Invoices                                                           */
        /* ------------------------------------------------------------------ */
        Schema::table('invoices', function (Blueprint $table) {

            if (!Schema::hasColumn('invoices', 'gstin')) {
                $table->string('gstin', 20)->nullable();
            }

            if (!Schema::hasColumn('invoices', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }

            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable();
            }
        });

        /* ------------------------------------------------------------------ */
        /* Payments                                                           */
        /* ------------------------------------------------------------------ */
        Schema::table('payments', function (Blueprint $table) {

            if (!Schema::hasColumn('payments', 'gateway')) {
                $table->string('gateway', 30)->nullable(); // stripe, razorpay
            }

            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->json('gateway_response')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'gateway_response')) {
                $table->dropColumn('gateway_response');
            }
            if (Schema::hasColumn('payments', 'gateway')) {
                $table->dropColumn('gateway');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('invoices', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
            if (Schema::hasColumn('invoices', 'gstin')) {
                $table->dropColumn('gstin');
            }
        });

        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'dimensions')) {
                $table->dropColumn('dimensions');
            }
            if (Schema::hasColumn('shipments', 'packages_count')) {
                $table->dropColumn('packages_count');
            }
            if (Schema::hasColumn('shipments', 'estimated_delivery_date')) {
                $table->dropColumn('estimated_delivery_date');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'channel')) {
                $table->dropColumn('channel');
            }
            if (Schema::hasColumn('orders', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('orders', 'tags')) {
                $table->dropColumn('tags');
            }
        });
    }
};
