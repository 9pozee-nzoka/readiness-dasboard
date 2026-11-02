<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map old statuses to new simplified statuses
        $statusMapping = [
            'not_started' => 'not_started',
            'in_progress' => 'in_progress',
            'under_deliberation' => 'in_progress',
            'awaiting_approval' => 'in_progress',
            'approved' => 'completed',
            'procurement_ongoing' => 'in_progress',
            'blocked' => 'in_progress',
            'escalated' => 'in_progress',
            'completed' => 'completed',
        ];

        foreach ($statusMapping as $oldStatus => $newStatus) {
            if ($oldStatus !== $newStatus) {
                DB::table('requirements')
                    ->where('status', $oldStatus)
                    ->update(['status' => $newStatus]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reliably reverse this migration
        // as we've lost information about the original statuses
    }
};
