<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->unsignedBigInteger('marketing_mail_service_id')->nullable()->after('id');
            $table->unsignedBigInteger('marketing_signature_id')->nullable()->after('marketing_mail_service_id');
            $table->index('marketing_mail_service_id', 'marketing_emails_mail_service_index');
            $table->index('marketing_signature_id', 'marketing_emails_signature_index');
        });

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->foreign('marketing_mail_service_id', 'marketing_emails_mail_service_foreign')
                ->references('id')->on('marketing_mail_services')->nullOnDelete();
            $table->foreign('marketing_signature_id', 'marketing_emails_signature_foreign')
                ->references('id')->on('marketing_signatures')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->dropForeign('marketing_emails_mail_service_foreign');
            $table->dropForeign('marketing_emails_signature_foreign');
            $table->dropColumn(['marketing_mail_service_id', 'marketing_signature_id']);
        });
    }
};
