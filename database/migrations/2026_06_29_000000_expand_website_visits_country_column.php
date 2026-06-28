<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->string('country')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->string('country', 10)->nullable()->change();
        });
    }
};
