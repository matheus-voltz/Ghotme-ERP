<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_id')->constrained('service_plans')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->integer('quantity')->default(1); // Quantidade deste serviço inclusa no período
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_items');
    }
};
