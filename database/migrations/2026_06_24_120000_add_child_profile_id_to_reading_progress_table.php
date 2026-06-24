<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreignId('child_profile_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('child_profiles')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'comic_id']);
            });
        }

        if (! Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unique(['child_profile_id', 'comic_id'], 'reading_progress_child_comic_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropUnique('reading_progress_child_comic_unique');
            });
        }

        if (! Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unique(['user_id', 'comic_id']);
            });
        }

        if (Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropConstrainedForeignId('child_profile_id');
            });
        }
    }
};
