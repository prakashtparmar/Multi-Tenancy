<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // System Identifiers
            $table->uuid('uuid')->unique();
            $table->string('customer_code')->unique(); // CUST-000001

            // Identity
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('mobile', 20)->unique();
            $table->string('email')->nullable()->index();
            // Identity (Extra Phones)
            $table->string('phone_number_2', 20)->nullable();
            $table->string('relative_phone', 20)->nullable();


            // ✅ Source (NEW)
            $table->string('source', 50)->nullable();

            // Classification
            $table->enum('type', ['farmer', 'buyer', 'vendor', 'dealer'])->default('farmer');
            $table->enum('category', ['individual', 'business'])->default('individual');

            // Business Details
            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable()->index();
            $table->string('pan_number')->nullable();

            // Agriculture Profile
            $table->decimal('land_area', 10, 2)->nullable();
            $table->string('land_unit')->default('acre');
            $table->json('crops')->nullable(); // Stores array of crop details
            $table->string('irrigation_type')->nullable(); // borewell, canal, rainfed

            // Financial / Credit
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->date('credit_valid_till')->nullable();

            // KYC & Compliance
            $table->string('aadhaar_last4')->nullable();
            $table->boolean('kyc_completed')->default(false);
            $table->timestamp('kyc_verified_at')->nullable();

            // Engagement
            $table->date('first_purchase_at')->nullable();
            $table->date('last_purchase_at')->nullable();
            $table->unsignedInteger('orders_count')->default(0);

            // Status & Control
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blacklisted')->default(false);
            $table->text('internal_notes')->nullable();
            $table->json('tags')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['type', 'is_active']);
            $table->index(['customer_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
