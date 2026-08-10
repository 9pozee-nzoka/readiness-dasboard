<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('schedule_category')->nullable()->after('type');
            $table->string('client')->nullable()->after('schedule_category');
            $table->date('event_date_end')->nullable()->after('event_date');
            $table->unsignedSmallInteger('participants_residential')->nullable()->after('venue');
            $table->unsignedSmallInteger('participants_non_residential')->nullable()->after('participants_residential');
            $table->string('coordinator')->nullable()->after('participants_non_residential');
            $table->string('programme_assistant')->nullable()->after('coordinator');
            $table->string('package_remarks')->nullable()->after('programme_assistant');
            $table->string('schedule_reference')->nullable()->after('package_remarks');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_category',
                'client',
                'event_date_end',
                'participants_residential',
                'participants_non_residential',
                'coordinator',
                'programme_assistant',
                'package_remarks',
                'schedule_reference',
            ]);
        });
    }
};
