<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_billing_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_account_id')->index();
            $table->string('paddle_subscription_id')->unique();
            $table->string('paddle_customer_id')->nullable()->index();
            $table->string('status', 30)->index();
            $table->string('price_id')->nullable();
            $table->string('product_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('next_billed_at')->nullable();
            $table->string('scheduled_change_action', 30)->nullable();
            $table->timestamp('scheduled_change_effective_at')->nullable();
            $table->timestamp('paddle_created_at')->nullable();
            $table->timestamp('last_event_at')->nullable();
            $table->json('items')->nullable();
            $table->json('custom_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_billing_subscriptions');
    }
};
