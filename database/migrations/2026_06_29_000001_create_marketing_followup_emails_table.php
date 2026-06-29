<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_followup_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_email_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marketing_template_id')->nullable()->constrained('marketing_templates')->nullOnDelete();
            $table->json('recipients');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('subject');
            $table->longText('body');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamp('scheduled_at')->index();
            $table->timestamp('sent_at')->nullable();
            $table->text('delivery_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_followup_emails');
    }
};
