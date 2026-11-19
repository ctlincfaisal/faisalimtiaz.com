<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_lead_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_account_id')->nullable()->index();
            $table->string('name', 80);
            $table->string('color', 20)->default('violet');
            $table->timestamps();
            $table->unique(['marketing_account_id', 'name'], 'marketing_lead_segments_account_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_segments');
    }
};
