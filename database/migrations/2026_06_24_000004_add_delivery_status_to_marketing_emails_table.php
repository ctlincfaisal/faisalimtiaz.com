<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('marketing_emails', 'delivery_status')) {
            return;
        }

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->string('delivery_status')->default('pending')->after('attachment_name');
            $table->text('delivery_error')->nullable()->after('delivery_status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('marketing_emails', 'delivery_status')) {
            return;
        }

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'delivery_error']);
        });
    }
};
