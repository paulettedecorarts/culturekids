<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 40);
            $table->unsignedBigInteger('content_id');
            $table->foreignId('panel_id')->nullable()->constrained('comic_panels')->cascadeOnDelete();
            $table->string('sub_item_key', 80)->nullable();
            $table->string('word');
            $table->string('translation')->nullable();
            $table->string('phonetic')->nullable();
            $table->integer('x_position')->nullable();
            $table->integer('y_position')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['content_type', 'content_id']);
            $table->index('panel_id');
        });

        if (! Schema::hasTable('panel_vocab_tags')) {
            return;
        }

        $panelComics = DB::table('comic_panels')->pluck('comic_id', 'id');

        DB::table('panel_vocab_tags')->orderBy('id')->chunk(200, function ($rows) use ($panelComics) {
            $inserts = [];
            foreach ($rows as $row) {
                $panelId = $row->panel_id;
                $inserts[] = [
                    'content_type' => 'story',
                    'content_id' => $panelComics[$panelId] ?? 0,
                    'panel_id' => $panelId,
                    'sub_item_key' => 'panel:'.$panelId,
                    'word' => $row->word,
                    'translation' => $row->translation,
                    'phonetic' => $row->phonetic,
                    'x_position' => $row->x_position,
                    'y_position' => $row->y_position,
                    'width' => $row->width,
                    'height' => $row->height,
                    'metadata' => $row->metadata,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            }
            if ($inserts !== []) {
                DB::table('content_translations')->insert($inserts);
            }
        });

        Schema::drop('panel_vocab_tags');
    }

    public function down(): void
    {
        Schema::create('panel_vocab_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panel_id')->constrained('comic_panels')->cascadeOnDelete();
            $table->string('word');
            $table->string('translation')->nullable();
            $table->string('phonetic')->nullable();
            $table->integer('x_position')->nullable();
            $table->integer('y_position')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('panel_id');
        });

        DB::table('content_translations')
            ->where('content_type', 'story')
            ->whereNotNull('panel_id')
            ->orderBy('id')
            ->chunk(200, function ($rows) {
                $inserts = [];
                foreach ($rows as $row) {
                    $inserts[] = [
                        'panel_id' => $row->panel_id,
                        'word' => $row->word,
                        'translation' => $row->translation,
                        'phonetic' => $row->phonetic,
                        'x_position' => $row->x_position,
                        'y_position' => $row->y_position,
                        'width' => $row->width,
                        'height' => $row->height,
                        'metadata' => $row->metadata,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ];
                }
                if ($inserts !== []) {
                    DB::table('panel_vocab_tags')->insert($inserts);
                }
            });

        Schema::dropIfExists('content_translations');
    }
};
