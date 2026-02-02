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
        Schema::table('products', function (Blueprint $col) {
            $col->string('default_discount_type')->nullable()->default('fixed')->after('price');
            $col->decimal('default_discount_value', 15, 2)->nullable()->default(0)->after('default_discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $col) {
            $col->dropColumn(['default_discount_type', 'default_discount_value']);
        });
    }
};
