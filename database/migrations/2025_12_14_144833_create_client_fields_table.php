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
        Schema::create('client_fields', function (Blueprint $table) {
            $table->id();
            $table->string('segment'); // oficina, clinica, loja
            $table->string('label');
            $table->string('field_key'); // ex: placa, convenio
            $table->string('field_type'); // text, number, date, select
            $table->boolean('required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_fields');
    }
};
