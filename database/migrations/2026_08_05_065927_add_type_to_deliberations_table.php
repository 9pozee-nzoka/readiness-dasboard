<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliberations', function (Blueprint $table) {
            // deliberation | consultation
            // consultation = employee asking HOD for direction on a specific requirement
            $table->string('type')->default('deliberation')->after('id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('deliberations', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropColumn('type');
        });
    }
};
