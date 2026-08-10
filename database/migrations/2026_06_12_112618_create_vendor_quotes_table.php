<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('requirement_id')->constrained('requirements')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('details')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();

            $table->index(['requirement_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotes');
    }
};
