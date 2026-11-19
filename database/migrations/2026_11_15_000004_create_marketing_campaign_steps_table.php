<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaign_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_account_id')->nullable()->index();
            $table->unsignedBigInteger('marketing_campaign_id')->index();
            $table->unsignedInteger('step_order');
            $table->string('step_type', 24)->default('email');
            $table->unsignedInteger('wait_days')->default(0);
            $table->string('subject', 255);
            $table->longText('body');
            $table->string('content_format', 16)->default('plain');
            $table->unsignedBigInteger('marketing_template_id')->nullable()->index();
            $table->unsignedBigInteger('marketing_mail_service_id')->nullable()->index();
            $table->unsignedBigInteger('marketing_signature_id')->nullable()->index();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamps();
            $table->unique(['marketing_campaign_id', 'step_order'], 'marketing_campaign_steps_campaign_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaign_steps');
    }
};
