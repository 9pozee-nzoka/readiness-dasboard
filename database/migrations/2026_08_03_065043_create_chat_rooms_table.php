<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('department'); // department | event | general
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'department_id']);
            $table->index(['type', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_rooms');
    }
};
