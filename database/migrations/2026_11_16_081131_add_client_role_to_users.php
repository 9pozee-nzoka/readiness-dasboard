<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role enum to include 'client'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'director', 'hod', 'employee', 'client') NOT NULL DEFAULT 'employee'");
    }

    public function down(): void
    {
        // Remove 'client' from enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'director', 'hod', 'employee') NOT NULL DEFAULT 'employee'");
    }
};
