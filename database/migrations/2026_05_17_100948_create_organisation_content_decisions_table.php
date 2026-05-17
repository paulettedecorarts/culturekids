<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organisation_content_decisions')) {
            Schema::create('organisation_content_decisions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
                $table->string('content_type', 32);
                $table->unsignedBigInteger('content_id');
                $table->string('decision');
                $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['organisation_id', 'content_type', 'content_id'], 'org_cnt_dec_unique');
                $table->index(['organisation_id', 'content_type', 'decision'], 'org_cnt_dec_lookup');
            });
        } else {
            $this->ensureIndexes();
        }

        $this->backfillLegacyDecisions();
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_content_decisions');
    }

    private function ensureIndexes(): void
    {
        if (! $this->indexExists('organisation_content_decisions', 'org_cnt_dec_unique')) {
            Schema::table('organisation_content_decisions', function (Blueprint $table) {
                $table->unique(['organisation_id', 'content_type', 'content_id'], 'org_cnt_dec_unique');
            });
        }

        if (! $this->indexExists('organisation_content_decisions', 'org_cnt_dec_lookup')) {
            Schema::table('organisation_content_decisions', function (Blueprint $table) {
                $table->index(['organisation_id', 'content_type', 'decision'], 'org_cnt_dec_lookup');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return $rows !== [];
    }

    private function backfillLegacyDecisions(): void
    {
        if (Schema::hasTable('organisation_comic_decisions')) {
            $rows = DB::table('organisation_comic_decisions')->get();
            foreach ($rows as $row) {
                DB::table('organisation_content_decisions')->insertOrIgnore([
                    'organisation_id' => $row->organisation_id,
                    'content_type' => 'story',
                    'content_id' => $row->comic_id,
                    'decision' => $row->decision,
                    'decided_by' => $row->decided_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }

        if (Schema::hasTable('organisation_song_decisions')) {
            $rows = DB::table('organisation_song_decisions')->get();
            foreach ($rows as $row) {
                DB::table('organisation_content_decisions')->insertOrIgnore([
                    'organisation_id' => $row->organisation_id,
                    'content_type' => 'song',
                    'content_id' => $row->song_id,
                    'decision' => $row->decision,
                    'decided_by' => $row->decided_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }
};
