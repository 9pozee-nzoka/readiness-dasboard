<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_type_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');                                    // e.g. "Meeting", "Ceremony"
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['event_type', 'department_id']);
            $table->unique(['event_type', 'department_id', 'description'], 'etr_type_dept_desc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_type_requirements');
    }
};
