<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $comicsId = DB::table('modules')->where('key', 'comics')->value('id');
        $storiesId = DB::table('modules')->where('key', 'stories')->value('id');

        if ($comicsId === null) {
            return;
        }

        if ($storiesId === null) {
            DB::table('modules')->where('id', $comicsId)->update([
                'key' => 'stories',
                'name' => 'Stories',
                'description' => 'Panel stories and comic reader',
            ]);

            return;
        }

        $comicsPivots = DB::table('module_organisation')->where('module_id', $comicsId)->get();

        foreach ($comicsPivots as $pivot) {
            $existing = DB::table('module_organisation')
                ->where('organisation_id', $pivot->organisation_id)
                ->where('module_id', $storiesId)
                ->first();

            if ($existing) {
                DB::table('module_organisation')->where('id', $pivot->id)->delete();
            } else {
                DB::table('module_organisation')->where('id', $pivot->id)->update([
                    'module_id' => $storiesId,
                ]);
            }
        }

        DB::table('modules')->where('id', $comicsId)->delete();
    }

    public function down(): void
    {
        $stories = DB::table('modules')->where('key', 'stories')->first();

        if ($stories && ! DB::table('modules')->where('key', 'comics')->exists()) {
            DB::table('modules')->where('id', $stories->id)->update([
                'key' => 'comics',
                'name' => 'Comics',
            ]);
        }
    }
};
