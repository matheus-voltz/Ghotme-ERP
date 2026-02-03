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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['in', 'out', 'adjustment']); // in = Entrada, out = Saída, adjustment = Ajuste
            $table->integer('quantity'); // Quantidade movimentada (positiva)
            $table->decimal('unit_price', 10, 2)->nullable(); // Preço unitário no momento da movimentação
            $table->string('reason')->nullable(); // Ex: "Compra", "Uso em OS #10", "Ajuste de inventário"
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Quem realizou
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};