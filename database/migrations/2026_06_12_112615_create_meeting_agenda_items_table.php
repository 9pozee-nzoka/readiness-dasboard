<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_agenda_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained('requirements')->nullOnDelete();
            $table->string('topic');
            $table->text('decision')->nullable();
            $table->string('status')->default('pending'); // pending, discussed, resolved
            $table->timestamps();

            $table->index('meeting_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_agenda_items');
    }
};
