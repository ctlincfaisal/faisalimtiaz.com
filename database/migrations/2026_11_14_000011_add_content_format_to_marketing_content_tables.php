<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['marketing_emails', 'marketing_followup_emails'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('content_format', 10)->default('plain')->after('body');
            });
        }

        Schema::table('marketing_templates', function (Blueprint $table) {
            $table->string('content_format', 10)->default('plain')->after('content');
        });
    }

    public function down(): void
    {
        foreach (['marketing_templates', 'marketing_emails', 'marketing_followup_emails'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('content_format');
            });
        }
    }
};
