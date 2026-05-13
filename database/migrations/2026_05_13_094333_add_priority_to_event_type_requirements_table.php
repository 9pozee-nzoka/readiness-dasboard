<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_type_requirements', function (Blueprint $table) {
            $table->string('priority')->default('medium')->after('description'); // critical|high|medium|low
            $table->unsignedSmallInteger('deadline_days_before')->nullable()->after('priority'); // days before event date
        });
    }

    public function down(): void
    {
        Schema::table('event_type_requirements', function (Blueprint $table) {
            $table->dropColumn(['priority', 'deadline_days_before']);
        });
    }
};
