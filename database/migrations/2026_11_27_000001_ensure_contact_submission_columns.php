<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        if (! Schema::hasColumn('contacts', 'project_type')) {
            Schema::table('contacts', function (Blueprint $table): void {
                $table->string('project_type')->nullable();
            });
        }

        if (! Schema::hasColumn('contacts', 'budget_range')) {
            Schema::table('contacts', function (Blueprint $table): void {
                $table->string('budget_range')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Keep these columns on rollback because they may have been created by
        // the original contact-schema migration or contain submitted data.
    }
};
