<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('marketing_email_group_contacts', 'project')) {
            Schema::table('marketing_email_group_contacts', function (Blueprint $table) {
                $table->string('project')->nullable();
            });
        }

        DB::table('marketing_email_group_contacts')
            ->whereRaw("TRIM(COALESCE(project, '')) = ''")
            ->where('name', 'like', '% - %')
            ->select(['id', 'name'])
            ->chunkById(250, function ($contacts) {
                foreach ($contacts as $contact) {
                    $separator = strrpos($contact->name, ' - ');
                    $name = trim(substr($contact->name, 0, $separator));
                    $project = trim(substr($contact->name, $separator + 3));

                    if ($name === '' || $project === '') {
                        continue;
                    }

                    DB::table('marketing_email_group_contacts')->where('id', $contact->id)->update([
                        'name' => $name,
                        'project' => $project,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('marketing_email_group_contacts', function (Blueprint $table) {
            $table->dropColumn('project');
        });
    }
};
