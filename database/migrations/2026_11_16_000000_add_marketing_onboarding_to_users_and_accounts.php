<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_accounts', function (Blueprint $table) {
            $table->string('plan', 30)->default('free')->after('slug');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('marketing_onboarding_completed_at')->nullable()->after('theme');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('marketing_onboarding_completed_at');
        });

        Schema::table('marketing_accounts', function (Blueprint $table) {
            $table->dropColumn('plan');
        });
    }
};
