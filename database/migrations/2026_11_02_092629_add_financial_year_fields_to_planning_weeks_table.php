<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->string('financial_year', 20)->nullable()->after('label')->comment('e.g., 2026/2027');
            $table->unsignedTinyInteger('week_number')->nullable()->after('financial_year')->comment('Week 1-52 within FY');

            $table->index(['financial_year', 'week_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planning_weeks', function (Blueprint $table) {
            $table->dropIndex(['financial_year', 'week_number']);
            $table->dropColumn(['financial_year', 'week_number']);
        });
    }
};
