<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->string('icon')->nullable(); // Ex: ti-tools-kitchen-2
            $table->integer('order')->default(0);
            $table->string('type')->default('product'); // product (prontos), ingredient (monte o seu), beverage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('menu_category_id')->nullable()->constrained('menu_categories')->onDelete('set null');
            $table->boolean('is_ingredient')->default(false); // Para identificar o que é opcional do hot dog
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('menu_category_id');
            $table->dropColumn('is_ingredient');
        });
        Schema::dropIfExists('menu_categories');
    }
};
