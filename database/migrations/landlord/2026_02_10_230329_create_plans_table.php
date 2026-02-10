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
        Schema::connection('landlord')->create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('max_funcionarios')->default(5);
            $table->integer('max_users')->default(1);
            $table->integer('max_products')->default(100);
            $table->decimal('valor_mensal', 10, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('landlord')->dropIfExists('plans');
    }
};
