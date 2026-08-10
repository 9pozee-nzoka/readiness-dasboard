<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliberations', function (Blueprint $table) {
            $table->string('outcome')->nullable()->after('status');
            $table->foreignId('escalated_to')->nullable()->after('outcome')->constrained('users')->nullOnDelete();
            $table->timestamp('escalated_at')->nullable()->after('escalated_to');

            $table->index(['status', 'decision_due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('deliberations', function (Blueprint $table) {
            $table->dropIndex(['status', 'decision_due_date']);

            $table->dropForeign(['escalated_to']);
            $table->dropColumn(['outcome', 'escalated_to', 'escalated_at']);
        });
    }
};
