<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 80)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('url');
            $table->string('path')->index();
            $table->string('element')->nullable();
            $table->string('element_text', 500)->nullable();
            $table->unsignedInteger('x')->nullable();
            $table->unsignedInteger('y')->nullable();
            $table->timestamp('clicked_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_clicks');
    }
};
