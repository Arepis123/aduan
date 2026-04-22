<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'closing_remark')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->text('closing_remark')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumnIfExists('closing_remark');
        });
    }
};
