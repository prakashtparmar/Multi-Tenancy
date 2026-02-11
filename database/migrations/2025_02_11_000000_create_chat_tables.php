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
        Schema::create('chat_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->json('members_ids')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('user_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('group_id')->default(0);
            $table->unsignedBigInteger('parent_message_id')->nullable();
            $table->unsignedBigInteger('forward_msg_id')->default(0);
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('attachment')->nullable();
            $table->string('s3_url')->nullable();
            $table->boolean('starred')->default(false);
            $table->timestamps();
        });

        Schema::create('user_chat_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('recipient_group_id')->default(0);
            $table->tinyInteger('is_read')->default(0);
            $table->dateTime('seen_date')->nullable();
            $table->timestamps();
        });

        Schema::create('user_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->tinyInteger('media_type')->nullable(); // 1=Image, 2=File, etc.
            $table->string('original_name')->nullable();
            $table->string('imagename')->nullable();
            $table->string('size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_media');
        Schema::dropIfExists('user_chat_recipients');
        Schema::dropIfExists('user_chats');
        Schema::dropIfExists('chat_groups');
    }
};
