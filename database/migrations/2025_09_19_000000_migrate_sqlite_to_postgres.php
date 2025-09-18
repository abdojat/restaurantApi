<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is specifically for migrating from SQLite to PostgreSQL
        // It will be run automatically when you deploy with PostgreSQL
        
        // The existing migrations will create the tables
        // The seeder will populate them with the same data
        
        // No additional schema changes needed as Laravel handles the differences
        // between SQLite and PostgreSQL automatically
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it's a one-time database switch
    }
};
