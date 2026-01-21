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
        // First, add the new emails column if it doesn't exist
        if (!Schema::hasColumn('departments', 'emails')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->json('emails')->nullable()->after('description');
            });
        }

        // Migrate existing email data to emails array (if email column exists)
        if (Schema::hasColumn('departments', 'email')) {
            DB::table('departments')->whereNotNull('email')->orderBy('id')->each(function ($department) {
                DB::table('departments')
                    ->where('id', $department->id)
                    ->update(['emails' => json_encode([$department->email])]);
            });

            // Drop the old email column
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('email')->nullable()->after('description');
        });

        // Migrate first email back to email column
        DB::table('departments')->whereNotNull('emails')->orderBy('id')->each(function ($department) {
            $emails = json_decode($department->emails, true);
            if (!empty($emails)) {
                DB::table('departments')
                    ->where('id', $department->id)
                    ->update(['email' => $emails[0]]);
            }
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('emails');
        });
    }
};
