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
        Schema::create('ai_insights', function (Blueprint $col) {
            $col->id();
            $col->foreignId('company_id')->constrained()->onDelete('cascade');
            $col->string('agent_name');
            $col->string('title');
            $col->text('observation');
            $col->text('recommendation')->nullable();
            $col->string('status')->default('ok');
            $col->timestamp('read_at')->nullable();
            $col->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_insights');
    }
};
