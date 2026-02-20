<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('company_id')->index();
            $blueprint->string('entity_type')->index(); // 'Clients', 'Vehicles', 'OrdemServico', etc.
            $blueprint->string('name'); // Ex: "Raça do Animal", "BTU", "Senha Wi-Fi"
            $blueprint->string('type')->default('text'); // text, number, date, select, checkbox, textarea
            $blueprint->json('options')->nullable(); // Para campos do tipo 'select'
            $blueprint->boolean('required')->default(false);
            $blueprint->integer('order')->default(0);
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();

            $blueprint->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
