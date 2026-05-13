<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planning_weeks', function (Blueprint $table) {
            $table->id();
            $table->string('label');          // e.g. "Week 20 – 12 May 2026"
            $table->date('week_start');
            $table->date('week_end');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index('week_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planning_weeks');
    }
};
