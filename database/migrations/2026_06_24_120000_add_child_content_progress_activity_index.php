<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_content_progress', function (Blueprint $table) {
            $table->index(
                ['child_profile_id', 'last_activity_at'],
                'child_content_progress_child_activity_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('child_content_progress', function (Blueprint $table) {
            $table->dropIndex('child_content_progress_child_activity_idx');
        });
    }
};
