<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_followup_email_opens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_followup_email_id');
            $table->foreign('marketing_followup_email_id', 'followup_email_opens_followup_id_fk')
                ->references('id')
                ->on('marketing_followup_emails')
                ->cascadeOnDelete();
            $table->string('email')->index();
            $table->uuid('tracking_id')->unique();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_followup_email_opens');
    }
};
