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
        Schema::create('interaction_outcomes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable(); // e.g., 'call', 'visit', 'order'
            $table->string('color')->nullable(); // e.g., 'bg-green-100 text-green-800'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default outcomes
        DB::table('interaction_outcomes')->insert([
            ['name' => 'Answered', 'type' => 'call', 'color' => 'bg-green-100 text-green-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Not Answered', 'type' => 'call', 'color' => 'bg-red-100 text-red-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Callback Requested', 'type' => 'call', 'color' => 'bg-blue-100 text-blue-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wrong Number', 'type' => 'call', 'color' => 'bg-gray-100 text-gray-800', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Order Completed', 'type' => 'order', 'color' => 'bg-purple-100 text-purple-800', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_outcomes');
    }
};
