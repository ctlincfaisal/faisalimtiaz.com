<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('marketing_campaigns', 'send_rate_per_minute')) {
            Schema::table('marketing_campaigns', function (Blueprint $table) {
                $table->unsignedTinyInteger('send_rate_per_minute')->default(5)->after('stop_on_reply');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('marketing_campaigns', 'send_rate_per_minute')) {
            Schema::table('marketing_campaigns', function (Blueprint $table) {
                $table->dropColumn('send_rate_per_minute');
            });
        }
    }
};
