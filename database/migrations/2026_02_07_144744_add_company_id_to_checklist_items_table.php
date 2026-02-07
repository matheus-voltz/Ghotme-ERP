<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('checklist_items')) {
            Schema::table('checklist_items', function (Blueprint $table) {
                if (!Schema::hasColumn('checklist_items', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('checklist_items', function (Blueprint $table) {
            if (Schema::hasColumn('checklist_items', 'company_id')) {
                $table->dropColumn('company_id');
            }
        });
    }
};