<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('description'); // critical|high|medium|low
            $table->date('deadline')->nullable()->after('priority');
            $table->boolean('is_escalated')->default(false)->after('deadline');
            $table->timestamp('escalated_at')->nullable()->after('is_escalated');

            $table->index('priority');
            $table->index('deadline');
            $table->index('is_escalated');
        });
    }

    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropIndex(['deadline']);
            $table->dropIndex(['is_escalated']);
            $table->dropColumn(['priority', 'deadline', 'is_escalated', 'escalated_at']);
        });
    }
};
