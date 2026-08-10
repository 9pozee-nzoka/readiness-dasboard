<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->integer('quantity_allocated')->default(1);
            $table->string('status')->default('requested'); // requested, confirmed, returned
            $table->timestamps();

            $table->unique(['event_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_assets');
    }
};
