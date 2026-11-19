<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_account_id')->nullable()->index();
            $table->string('first_name', 120)->nullable();
            $table->string('last_name', 120)->nullable();
            $table->string('company', 180)->nullable();
            $table->string('email', 254);
            $table->string('status', 32)->default('new')->index();
            $table->string('segment', 80)->nullable()->index();
            $table->timestamps();
            $table->unique(['marketing_account_id', 'email'], 'marketing_leads_account_email_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_leads');
    }
};
