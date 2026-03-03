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
        Schema::create('service_addon_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ex: "Adicionais", "Escolha o Pão"
            $table->enum('selection_type', ['single', 'multiple'])->default('multiple'); // se a pessoa pode marcar vários (checkboxes) ou um só (radio)
            $table->integer('min_options')->default(0); // se 1, obrigatório
            $table->integer('max_options')->nullable(); // se null, sem limite (no caso múltiplo)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_addon_groups');
    }
};
