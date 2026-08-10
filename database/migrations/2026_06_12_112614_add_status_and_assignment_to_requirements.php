<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->string('status')->default('not_started')->after('priority');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('responsible_officer');
            $table->boolean('requires_deliberation')->default(false)->after('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['status', 'assigned_to', 'requires_deliberation']);
        });
    }
};
