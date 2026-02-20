<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('custom_field_id');
            $blueprint->unsignedBigInteger('model_id'); // ID do registro (Cliente #1, OS #10)
            $blueprint->string('model_type'); // Classe do modelo (App\Models\Clients, etc.)
            $blueprint->text('value')->nullable(); // Valor salvo (Ex: "Golden Retriever", "12000", "2023-01-01")
            $blueprint->timestamps();

            $blueprint->index(['model_id', 'model_type']);
            $blueprint->foreign('custom_field_id')->references('id')->on('custom_fields')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
