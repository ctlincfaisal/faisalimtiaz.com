<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_accounts', function (Blueprint $table) {
            $table->string('paddle_customer_id')->nullable()->index();
            $table->string('paddle_subscription_id')->nullable()->index();
            $table->string('paddle_subscription_status', 30)->nullable()->index();
            $table->timestamp('paddle_next_billed_at')->nullable();
            $table->string('paddle_scheduled_change_action', 30)->nullable();
            $table->timestamp('paddle_scheduled_change_effective_at')->nullable();
            $table->timestamp('paddle_last_event_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_accounts', function (Blueprint $table) {
            $table->dropIndex(['paddle_customer_id']);
            $table->dropIndex(['paddle_subscription_id']);
            $table->dropIndex(['paddle_subscription_status']);
            $table->dropColumn([
                'paddle_customer_id',
                'paddle_subscription_id',
                'paddle_subscription_status',
                'paddle_next_billed_at',
                'paddle_scheduled_change_action',
                'paddle_scheduled_change_effective_at',
                'paddle_last_event_at',
            ]);
        });
    }
};
