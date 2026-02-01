<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('enquiry'); // enquiry, follow_up, complaint
            $table->string('outcome')->nullable(); // no_order, pricing_concern, out_of_stock, etc.
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // store context like which products they enquired about
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_interactions');
    }
};
