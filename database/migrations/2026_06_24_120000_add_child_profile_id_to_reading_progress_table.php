<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (! Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unsignedBigInteger('child_profile_id')->nullable()->after('user_id');
            });
        }

        // MySQL requires dropping the foreign keys that depend on the
        // (user_id, comic_id) unique index before that index can be dropped.
        // SQLite (used in tests) cannot alter foreign keys after table
        // creation and does not need this dance.
        if ($driver === 'mysql') {
            if ($this->foreignKeyExists('reading_progress', 'user_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->dropForeign(['user_id']));
            }

            if ($this->foreignKeyExists('reading_progress', 'comic_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->dropForeign(['comic_id']));
            }

            if (Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->dropUnique(['user_id', 'comic_id']));
            }

            if (! $this->foreignKeyExists('reading_progress', 'user_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete());
            }

            if (! $this->foreignKeyExists('reading_progress', 'comic_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->foreign('comic_id')->references('id')->on('comics')->cascadeOnDelete());
            }
        }

        if (! Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->unique(['child_profile_id', 'comic_id'], 'reading_progress_child_comic_unique');
            });
        }

        if ($driver === 'mysql' && ! $this->foreignKeyExists('reading_progress', 'child_profile_id')) {
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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' && $this->foreignKeyExists('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', fn (Blueprint $table) => $table->dropForeign(['child_profile_id']));
        }

        if (Schema::hasIndex('reading_progress', 'reading_progress_child_comic_unique')) {
            Schema::table('reading_progress', fn (Blueprint $table) => $table->dropUnique('reading_progress_child_comic_unique'));
        }

        if ($driver === 'mysql') {
            if ($this->foreignKeyExists('reading_progress', 'user_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->dropForeign(['user_id']));
            }

            if ($this->foreignKeyExists('reading_progress', 'comic_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->dropForeign(['comic_id']));
            }

            if (! Schema::hasIndex('reading_progress', ['user_id', 'comic_id'])) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->unique(['user_id', 'comic_id']));
            }

            if (! $this->foreignKeyExists('reading_progress', 'user_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete());
            }

            if (! $this->foreignKeyExists('reading_progress', 'comic_id')) {
                Schema::table('reading_progress', fn (Blueprint $table) => $table->foreign('comic_id')->references('id')->on('comics')->cascadeOnDelete());
            }
        }

        if (Schema::hasColumn('reading_progress', 'child_profile_id')) {
            Schema::table('reading_progress', function (Blueprint $table) {
                $table->dropColumn('child_profile_id');
            });
        }
    }

    /**
     * Driver-agnostic foreign-key existence check (works on MySQL and SQLite).
     */
    private function foreignKeyExists(string $table, string $column): bool
    {
        foreach (Schema::getForeignKeys($table) as $foreignKey) {
            if (in_array($column, $foreignKey['columns'] ?? [], true)) {
                return true;
            }
        }

        return false;
    }
};
