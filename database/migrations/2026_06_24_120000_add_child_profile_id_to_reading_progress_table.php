<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unsignedBigInteger('child_profile_id')->nullable()->after('user_id');
            });
        }

        if ($this->foreignKeyExists('reading_progress', 'user_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($this->foreignKeyExists('reading_progress', 'comic_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropForeign(['comic_id']);
            });
        }

        if (Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'comic_id']);
            });
        }

        if (! $this->foreignKeyExists('reading_progress', 'user_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! $this->foreignKeyExists('reading_progress', 'comic_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreign('comic_id')->references('id')->on('comics')->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unique(['child_profile_id', 'comic_id'], 'reading_progress_child_comic_unique');
            });
        }

        if (! $this->foreignKeyExists('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreign('child_profile_id')
                    ->references('id')
                    ->on('child_profiles')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropForeign(['child_profile_id']);
            });
        }

        if (Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropUnique('reading_progress_child_comic_unique');
            });
        }

        if ($this->foreignKeyExists('reading_progress', 'user_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        }

        if ($this->foreignKeyExists('reading_progress', 'comic_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropForeign(['comic_id']);
            });
        }

        if (! Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unique(['user_id', 'comic_id']);
            });
        }

        if (! $this->foreignKeyExists('reading_progress', 'user_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! $this->foreignKeyExists('reading_progress', 'comic_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->foreign('comic_id')->references('id')->on('comics')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropColumn('child_profile_id');
            });
        }
    }

    private function foreignKeyExists(string $table, string $column): bool
    {
        $database = Schema::getConnection()->getDatabaseName();

        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $column)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};
