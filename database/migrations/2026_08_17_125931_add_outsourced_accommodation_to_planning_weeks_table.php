<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->integer('outsourced_accommodation')->default(0)->after('schedule_reference');
        });
    }

    public function down(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->dropColumn('outsourced_accommodation');
        });
    }
};
