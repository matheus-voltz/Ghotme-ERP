<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Budget;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            if (!Schema::hasColumn('budgets', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique()->after('id');
            }
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('approval_ip')->nullable();
            $table->text('rejection_reason')->nullable();
        });

        // Preencher UUIDs para orçamentos existentes
        Budget::all()->each(function ($budget) {
            $budget->update(['uuid' => (string) Str::uuid()]);
        });
    }

    public function down(): void
    {
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropColumn(['uuid', 'approved_at', 'rejected_at', 'approval_ip', 'rejection_reason']);
        });
    }
};