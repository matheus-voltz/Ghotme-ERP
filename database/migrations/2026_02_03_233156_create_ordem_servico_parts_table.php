<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordem_servico_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ordem_servico_id')->constrained('ordem_servicos')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items');
            $table->decimal('price', 10, 2); // Preço de venda na época
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordem_servico_parts');
    }
};