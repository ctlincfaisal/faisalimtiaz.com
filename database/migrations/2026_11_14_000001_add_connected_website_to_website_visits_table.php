<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('website_visits', 'connected_website_id')) {
            Schema::table('website_visits', function (Blueprint $table) {
                $table->unsignedBigInteger('connected_website_id')->nullable()->after('id');
            });
        }

        if (! $this->hasConnectedWebsiteForeignKey('website_visits')) {
            Schema::table('website_visits', function (Blueprint $table) {
                $table->foreign('connected_website_id', 'website_visits_connected_website_id_foreign')
                    ->references('id')->on('connected_websites')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->dropForeign(['connected_website_id']);
            $table->dropColumn('connected_website_id');
        });
    }

    private function hasConnectedWebsiteForeignKey(string $table): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA foreign_key_list('{$table}')"))
                ->contains(fn ($foreignKey) => $foreignKey->table === 'connected_websites' && $foreignKey->from === 'connected_website_id');
        }

        return DB::selectOne(
            'select 1 from information_schema.KEY_COLUMN_USAGE where CONSTRAINT_SCHEMA = DATABASE() and TABLE_NAME = ? and COLUMN_NAME = ? and REFERENCED_TABLE_NAME = ? limit 1',
            [$table, 'connected_website_id', 'connected_websites']
        ) !== null;
    }
};
