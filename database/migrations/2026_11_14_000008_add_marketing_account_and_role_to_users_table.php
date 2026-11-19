<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->unsignedBigInteger('marketing_account_id')->nullable()->after('id');
            $table->string('role', 30)->default('admin')->after('marketing_account_id')->index();
            $table->index('marketing_account_id', 'users_marketing_account_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('marketing_account_id', 'users_marketing_account_foreign')
                ->references('id')->on('marketing_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_marketing_account_foreign');
            $table->dropColumn(['username', 'marketing_account_id', 'role']);
        });
    }
};
