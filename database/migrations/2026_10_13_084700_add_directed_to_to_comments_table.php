<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('directed_to')->nullable()->after('via_email')->comment('Who the comment is directed to: auto, director, staff, specific_user_id');
            $table->unsignedBigInteger('directed_to_user_id')->nullable()->after('directed_to');

            $table->foreign('directed_to_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['directed_to_user_id']);
            $table->dropColumn(['directed_to', 'directed_to_user_id']);
        });
    }
};
