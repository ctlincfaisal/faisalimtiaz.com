<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('marketing_email_opens')
            || ! DB::getSchemaBuilder()->hasTable('marketing_emails')
            || ! DB::getSchemaBuilder()->hasColumn('marketing_email_opens', 'marketing_account_id')
            || ! DB::getSchemaBuilder()->hasColumn('marketing_emails', 'marketing_account_id')) {
            return;
        }

        DB::table('marketing_email_opens as opens')
            ->join('marketing_emails as emails', 'emails.id', '=', 'opens.marketing_email_id')
            ->whereNull('opens.marketing_account_id')
            ->whereNotNull('emails.marketing_account_id')
            ->select(['opens.id', 'emails.marketing_account_id'])
            ->orderBy('opens.id')
            ->chunk(500, function ($trackers): void {
                foreach ($trackers as $tracker) {
                    DB::table('marketing_email_opens')
                        ->where('id', $tracker->id)
                        ->whereNull('marketing_account_id')
                        ->update(['marketing_account_id' => $tracker->marketing_account_id]);
                }
            }, 'opens.id', 'id');
    }

    public function down(): void
    {
        // Existing account ownership must not be removed on rollback.
    }
};
