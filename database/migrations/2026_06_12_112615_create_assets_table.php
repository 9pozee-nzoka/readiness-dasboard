<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // venue, equipment, vehicle, staff
            $table->integer('quantity')->default(1);
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
