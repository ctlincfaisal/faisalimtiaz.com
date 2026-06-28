<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->string('country', 10)->nullable()->after('referrer');
            $table->string('region')->nullable()->after('country');
            $table->string('city')->nullable()->after('region');
            $table->string('postal')->nullable()->after('city');
            $table->string('timezone')->nullable()->after('postal');
            $table->decimal('latitude', 10, 7)->nullable()->after('timezone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('organization')->nullable()->after('longitude');
            $table->string('browser')->nullable()->after('organization');
            $table->string('browser_version')->nullable()->after('browser');
            $table->string('operating_system')->nullable()->after('browser_version');
            $table->string('device_type')->nullable()->after('operating_system');
        });
    }

    public function down(): void
    {
        Schema::table('website_visits', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'region',
                'city',
                'postal',
                'timezone',
                'latitude',
                'longitude',
                'organization',
                'browser',
                'browser_version',
                'operating_system',
                'device_type',
            ]);
        });
    }
};
