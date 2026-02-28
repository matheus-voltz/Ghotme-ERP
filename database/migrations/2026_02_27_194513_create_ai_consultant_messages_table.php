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
        Schema::create('ai_consultant_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('ai_consultant_chats')->onDelete('cascade');
            $table->enum('role', ['user', 'assistant']);
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_consultant_messages');
    }
};
