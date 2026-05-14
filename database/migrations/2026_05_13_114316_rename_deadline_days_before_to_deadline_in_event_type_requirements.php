<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_type_requirements', function (Blueprint $table) {
            $table->dropColumn('deadline_days_before');
            $table->date('deadline')->nullable()->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('event_type_requirements', function (Blueprint $table) {
            $table->dropColumn('deadline');
            $table->unsignedSmallInteger('deadline_days_before')->nullable()->after('priority');
        });
    }
};
