<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tracks the level the user self-declared at registration (hod|employee)
            // null for admin/director who are created directly by admin
            $table->string('declared_level')->nullable()->after('department_id');

            // Admin must approve before the user can access the dashboard
            $table->boolean('is_approved')->default(false)->after('declared_level');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');

            $table->index('is_approved');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['declared_level', 'is_approved', 'approved_at', 'approved_by']);
        });
    }
};
