<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliberation_participant', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliberation_id')->constrained('deliberations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['deliberation_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliberation_participant');
    }
};
