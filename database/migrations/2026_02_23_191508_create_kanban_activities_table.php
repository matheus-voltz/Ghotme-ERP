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
        Schema::create('kanban_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanban_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // 'creation', 'move', 'update', 'comment'
            $table->text('description');
            $table->json('data')->nullable(); // Para guardar estado anterior/novo se necessário
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_activities');
    }
};
