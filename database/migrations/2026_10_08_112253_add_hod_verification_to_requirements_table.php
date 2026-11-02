<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add HOD verification fields per FR-10.
     * Completed requirements must be verified by HOD before counting toward readiness.
     */
    public function up(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->after('is_completed')->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
            $table->boolean('is_verified')->default(false)->after('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('requirements', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verified_by', 'verified_at', 'verification_notes', 'is_verified']);
        });
    }
};
