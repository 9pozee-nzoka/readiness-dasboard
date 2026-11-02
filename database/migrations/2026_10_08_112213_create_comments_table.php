<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Comments replace deliberations - threaded conversations on events and requirements.
     * Supports email and WhatsApp reply integration.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('requirement_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_comment_id')->nullable()->constrained('comments')->cascadeOnDelete();

            // Channel: system, email, whatsapp
            $table->string('channel')->default('system');
            $table->text('message');

            // For email/WhatsApp integration
            $table->string('external_reference')->nullable(); // Email message-id or WhatsApp msg id
            $table->string('sender_identifier')->nullable(); // Email address or phone number

            $table->timestamps();

            $table->index(['event_id', 'created_at']);
            $table->index(['requirement_id', 'created_at']);
            $table->index(['parent_comment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
