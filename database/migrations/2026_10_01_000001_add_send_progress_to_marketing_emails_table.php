<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('marketing_emails', 'sent_count')) {
            return;
        }

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->unsignedInteger('sent_count')->default(0)->after('recipient_count');
            $table->unsignedInteger('failed_count')->default(0)->after('sent_count');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('marketing_emails', 'sent_count')) {
            return;
        }

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->dropColumn(['sent_count', 'failed_count']);
        });
    }
};
