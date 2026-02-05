<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->string('village_name')->index();
            $table->string('pincode', 6)->index();
            $table->string('post_so_name')->nullable();
            $table->string('taluka_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('state_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
