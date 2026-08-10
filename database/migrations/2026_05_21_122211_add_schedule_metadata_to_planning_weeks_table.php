<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->string('campus')->nullable()->after('is_current');
            $table->string('schedule_reference')->nullable()->after('campus');
        });
    }

    public function down(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->dropColumn(['campus', 'schedule_reference']);
        });
    }
};
