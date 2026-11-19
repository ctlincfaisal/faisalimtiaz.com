<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_account_id')->nullable()->index();
            $table->string('name', 180);
            $table->string('description', 500)->nullable();
            $table->string('status', 24)->default('draft')->index();
            $table->string('audience_type', 24)->default('all_leads');
            $table->string('audience_segment', 80)->nullable();
            $table->string('audience_status', 32)->nullable();
            $table->json('manual_recipients')->nullable();
            $table->unsignedInteger('recipient_count')->default(0);
            $table->unsignedInteger('sequence_count')->default(1);
            $table->boolean('stop_on_reply')->default(true);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('current_step')->default(0);
            $table->unsignedInteger('pending_emails')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
