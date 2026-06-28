<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_templates', function (Blueprint $table) {
            $table->json('subject_options')->nullable()->after('subject');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_templates', function (Blueprint $table) {
            $table->dropColumn('subject_options');
        });
    }
};
