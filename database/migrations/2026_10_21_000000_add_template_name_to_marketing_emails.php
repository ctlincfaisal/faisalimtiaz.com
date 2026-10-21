<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->string('template_name')->nullable();
        });

        Schema::table('marketing_followup_emails', function (Blueprint $table) {
            $table->string('template_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_followup_emails', function (Blueprint $table) {
            $table->dropColumn('template_name');
        });

        Schema::table('marketing_emails', function (Blueprint $table) {
            $table->dropColumn('template_name');
        });
    }
};
