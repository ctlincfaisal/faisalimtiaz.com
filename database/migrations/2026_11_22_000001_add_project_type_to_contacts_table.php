<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contacts', 'project_type') || ! Schema::hasColumn('contacts', 'budget_range')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (! Schema::hasColumn('contacts', 'project_type')) {
                    $table->string('project_type')->nullable()->after('email');
                }
                if (! Schema::hasColumn('contacts', 'budget_range')) {
                    $table->string('budget_range')->nullable()->after('project_type');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contacts', 'project_type') || Schema::hasColumn('contacts', 'budget_range')) {
            Schema::table('contacts', function (Blueprint $table) {
                if (Schema::hasColumn('contacts', 'budget_range')) {
                    $table->dropColumn('budget_range');
                }
                if (Schema::hasColumn('contacts', 'project_type')) {
                    $table->dropColumn('project_type');
                }
            });
        }
    }
};
