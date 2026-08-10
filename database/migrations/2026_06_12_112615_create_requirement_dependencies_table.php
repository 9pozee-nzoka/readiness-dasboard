<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requirement_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
            $table->foreignId('depends_on_requirement_id')->constrained('requirements')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['requirement_id', 'depends_on_requirement_id'], 'req_deps_unique');
            $table->index('requirement_id', 'req_deps_req_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_dependencies');
    }
};
