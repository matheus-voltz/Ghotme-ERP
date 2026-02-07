<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vehicle_inspections')) {
            Schema::table('vehicle_inspections', function (Blueprint $table) {
                if (!Schema::hasColumn('vehicle_inspections', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicle_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('vehicle_inspections', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });
    }
};