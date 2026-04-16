<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->foreignId('ticket_log_id')->nullable()->after('ticket_reply_id')->constrained('ticket_logs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\TicketLog::class);
            $table->dropColumn('ticket_log_id');
        });
    }
};
