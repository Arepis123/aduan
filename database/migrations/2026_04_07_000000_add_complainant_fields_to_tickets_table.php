<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('complainant_company')->nullable()->after('requester_phone');
            $table->string('requester_email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('complainant_company');
            $table->string('requester_email')->nullable(false)->change();
        });
    }
};
