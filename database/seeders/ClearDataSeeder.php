<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearDataSeeder extends Seeder
{
    /**
     * Clear all data except users and permission tables.
     */
    public function run(): void
    {
        // Tables to preserve (will NOT be cleared)
        $preserveTables = [
            'migrations',
            'permissions',
            'roles',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
        ];

        // Tables to clear (in order to respect foreign keys)
        $tablesToClear = [
            'ticket_attachments',
            'ticket_replies',
            'tickets',
            'categories',
            'units',
            'departments',
            'sectors',
            'sessions',
            'password_reset_tokens',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'users',
        ];

        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        foreach ($tablesToClear as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->command->info("Cleared table: {$table}");
            } else {
                $this->command->warn("Table not found: {$table}");
            }
        }

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();

        $this->command->info("\nData cleared successfully!");
        $this->command->info("Preserved tables: " . implode(', ', $preserveTables));
    }
}
