<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_email_opens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_email_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('tracking_id')->unique();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->unsignedInteger('open_count')->default(0);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['marketing_email_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_email_opens');
    }
};
