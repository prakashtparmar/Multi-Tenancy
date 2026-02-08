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
        Schema::table('returns', function (Blueprint $table) {
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete()->after('refund_method');
            $table->timestamp('inspected_at')->nullable()->after('inspected_by');
            $table->decimal('refunded_amount', 12, 2)->nullable()->after('inspected_at');
        });

        Schema::table('return_items', function (Blueprint $table) {
            $table->decimal('quantity_received', 12, 3)->nullable()->after('quantity');
            $table->string('condition_received')->nullable()->after('condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_received', 'condition_received']);
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['inspected_by']);
            $table->dropColumn(['inspected_by', 'inspected_at', 'refunded_amount']);
        });
    }
};
