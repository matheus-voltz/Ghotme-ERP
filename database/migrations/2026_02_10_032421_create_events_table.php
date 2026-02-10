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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->boolean('all_day')->default(false);
            $table->string('url')->nullable();
            
            // Extended Props
            $table->string('calendar')->default('Business'); // Label (Business, Personal, etc)
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->json('guests')->nullable(); // Array de nomes ou IDs
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
