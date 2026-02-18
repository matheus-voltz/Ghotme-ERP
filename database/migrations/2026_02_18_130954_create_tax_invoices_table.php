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
        Schema::create('tax_invoices', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('company_id');
            $blueprint->unsignedBigInteger('ordem_servico_id')->nullable();
            $blueprint->string('invoice_type')->default('nfse'); // nfse ou nfe
            $blueprint->string('status')->default('pending'); // pending, processing, authorized, rejected, cancelled
            
            $blueprint->string('number')->nullable(); // Número da nota
            $blueprint->string('series')->nullable(); // Série da nota
            $blueprint->string('access_key')->nullable(); // Chave de acesso (NF-e)
            
            $blueprint->decimal('total_amount', 15, 2);
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            
            $blueprint->string('xml_url')->nullable();
            $blueprint->string('pdf_url')->nullable();
            $blueprint->text('error_message')->nullable();
            
            $blueprint->timestamp('issued_at')->nullable();
            $blueprint->timestamps();

            // Chaves estrangeiras
            $blueprint->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $blueprint->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_invoices');
    }
};
