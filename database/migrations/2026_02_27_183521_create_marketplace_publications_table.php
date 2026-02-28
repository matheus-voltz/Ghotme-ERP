<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->morphs('publishable'); // publishable_id, publishable_type (InventoryItem, Service)
            $table->string('platform'); // mercado_livre, olx, getninjas
            $table->string('external_id')->nullable(); // ID do anuncio no ML/OLX
            $table->string('external_url')->nullable(); // Link para o anuncio
            $table->string('status')->default('active'); // active, paused, closed, error
            $table->decimal('price', 10, 2)->nullable(); // Preco especifico para o marketplace
            $table->boolean('sync_stock')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_publications');
    }
};
