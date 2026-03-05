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
        Schema::create('ai_business_memories', function (Blueprint $col) {
            $col->id();
            $col->foreignId('company_id')->constrained()->onDelete('cascade');
            $col->string('key')->index(); // Ex: 'focus_of_the_month', 'owner_preference'
            $col->text('content');
            $col->integer('importance')->default(1); // 1-5, para priorizar fatos no prompt
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_business_memories');
    }
};
