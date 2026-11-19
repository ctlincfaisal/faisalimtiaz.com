<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('marketing_emails', 'marketing_campaign_id')) {
            Schema::table('marketing_emails', function (Blueprint $table) {
                $table->unsignedBigInteger('marketing_campaign_id')->nullable()->after('id')->index();
                $table->unsignedBigInteger('marketing_campaign_step_id')->nullable()->after('marketing_campaign_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('marketing_emails', 'marketing_campaign_id')) {
            Schema::table('marketing_emails', function (Blueprint $table) {
                $table->dropIndex('marketing_emails_marketing_campaign_id_index');
                $table->dropIndex('marketing_emails_marketing_campaign_step_id_index');
                $table->dropColumn(['marketing_campaign_id', 'marketing_campaign_step_id']);
            });
        }
    }
};
