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
        Schema::create('kanban_boards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->nullable(); // Para ids do jkanban (ex: board-id-1)
            $table->integer('order')->default(0); // Posição da coluna
            $table->timestamps();
        });

        Schema::create('kanban_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kanban_board_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Quem criou/responsável
            
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->string('badge_text')->nullable(); // Ex: UX, App
            $table->string('badge_color')->default('success'); // Ex: success, warning
            $table->json('assigned_to')->nullable(); // Array de nomes ou caminhos de avatar
            $table->json('attachments')->nullable(); // Array de nomes de arquivos
            $table->json('comments')->nullable(); // Simples array de comentários por enquanto
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_items');
        Schema::dropIfExists('kanban_boards');
    }
};
