<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_tribe_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tribe_id')->constrained()->cascadeOnDelete();
            $table->timestamp('approved_at')->useCurrent();
            $table->unique(['user_id', 'tribe_id']);
        });

        if (Schema::hasTable('child_profile_tribe')) {
            $rows = DB::table('child_profile_tribe')
                ->join('child_profiles', 'child_profiles.id', '=', 'child_profile_tribe.child_profile_id')
                ->select('child_profiles.user_id as parent_id', 'child_profile_tribe.tribe_id', 'child_profile_tribe.approved_at')
                ->distinct()
                ->get();

            foreach ($rows as $row) {
                DB::table('parent_tribe_approvals')->insertOrIgnore([
                    'user_id' => $row->parent_id,
                    'tribe_id' => $row->tribe_id,
                    'approved_at' => $row->approved_at ?? now(),
                ]);
            }

            Schema::drop('child_profile_tribe');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_tribe_approvals');
    }
};
