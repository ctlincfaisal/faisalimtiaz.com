<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'contacts', 'connected_websites', 'website_visits', 'website_clicks', 'marketing_credentials',
        'marketing_emails', 'marketing_email_opens', 'marketing_unsubscribes', 'marketing_templates',
        'marketing_followup_emails', 'marketing_followup_email_opens', 'marketing_email_group_contacts',
        'marketing_mail_services', 'marketing_signatures',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'marketing_account_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unsignedBigInteger('marketing_account_id')->nullable()->after('id');
                $table->index('marketing_account_id', $tableName.'_marketing_account_index');
            });
        }

        if (Schema::hasTable('marketing_email_group_contacts')) {
            Schema::table('marketing_email_group_contacts', function (Blueprint $table) {
                $table->dropUnique('marketing_email_group_contacts_email_unique');
                $table->unique(['marketing_account_id', 'email'], 'marketing_email_groups_account_email_unique');
            });
        }

        if (Schema::hasTable('marketing_unsubscribes')) {
            Schema::table('marketing_unsubscribes', function (Blueprint $table) {
                $table->dropUnique('marketing_unsubscribes_email_unique');
                $table->unique(['marketing_account_id', 'email'], 'marketing_unsubscribes_account_email_unique');
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables) as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'marketing_account_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropIndex($tableName.'_marketing_account_index');
                $table->dropColumn('marketing_account_id');
            });
        }
    }
};
