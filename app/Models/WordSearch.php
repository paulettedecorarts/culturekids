<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WordSearch extends Model
{
    protected $fillable = [
        'tribe_id', 'title', 'description', 'difficulty_level',
        'age_min', 'age_max', 'star_points', 'status',
        'grid_size', 'allow_diagonal', 'allow_reverse',
        'words', 'grid', 'word_positions',
        'cultural_note', 'language_code', 'metadata',
    ];

    protected $casts = [
        'age_min'         => 'integer',
        'age_max'         => 'integer',
        'star_points'     => 'integer',
        'grid_size'       => 'integer',
        'allow_diagonal'  => 'boolean',
        'allow_reverse'   => 'boolean',
        'words'           => 'array',
        'grid'            => 'array',
        'word_positions'  => 'array',
        'metadata'        => 'array',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (WordSearch $ws): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try { $ws->syncLegacyActivity(); }
            finally { self::$syncingLegacyActivity = false; }
        });

        static::deleted(function (WordSearch $ws): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'word_search')
                    ->where('metadata->legacy_word_search_id', $ws->id)
                    ->delete();
            } finally { self::$syncingLegacyActivity = false; }
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(WordSearchAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    /**
     * Generate the word search grid from the words list.
     * Places words in the grid, fills remaining cells with random letters.
     * Returns ['grid' => 2D array, 'word_positions' => array]
     */
    public function generateGrid(): array
    {
        $size      = $this->grid_size;
        $words     = collect($this->words ?? [])->pluck('word')->map(fn ($w) => strtoupper(trim($w)))->filter()->values()->toArray();
        $diagonal  = $this->allow_diagonal;
        $reverse   = $this->allow_reverse;

        // Directions: [row_delta, col_delta]
        $directions = [
            [0, 1],   // right
            [1, 0],   // down
        ];
        if ($diagonal) {
            $directions[] = [1, 1];   // diagonal down-right
            $directions[] = [1, -1];  // diagonal down-left
        }
        if ($reverse) {
            $directions[] = [0, -1];  // left
            $directions[] = [-1, 0];  // up
            if ($diagonal) {
                $directions[] = [-1, -1]; // diagonal up-left
                $directions[] = [-1, 1];  // diagonal up-right
            }
        }

        // Initialize empty grid
        $grid = [];
        for ($r = 0; $r < $size; $r++) {
            $grid[$r] = array_fill(0, $size, '');
        }

        $wordPositions = [];

        // Try to place each word
        foreach ($words as $word) {
            $wordLen = strlen($word);
            if ($wordLen > $size) continue;

            $placed   = false;
            $attempts = 0;

            while (!$placed && $attempts < 200) {
                $attempts++;
                $dir = $directions[array_rand($directions)];
                [$dr, $dc] = $dir;

                // Calculate valid start positions
                $startRow = $dr >= 0 ? 0 : $size - 1;
                $endRow   = $dr >= 0 ? $size - $wordLen * max(0, $dr) : $wordLen * abs($dr) - 1;
                $startCol = $dc >= 0 ? 0 : $size - 1;
                $endCol   = $dc >= 0 ? $size - $wordLen * max(0, $dc) : $wordLen * abs($dc) - 1;

                if ($endRow < $startRow || $endCol < $startCol) continue;

                $row = rand(min($startRow, $endRow), max($startRow, $endRow));
                $col = rand(min($startCol, $endCol), max($startCol, $endCol));

                // Check if word fits
                $cells = [];
                $fits  = true;
                for ($i = 0; $i < $wordLen; $i++) {
                    $r = $row + $i * $dr;
                    $c = $col + $i * $dc;
                    if ($r < 0 || $r >= $size || $c < 0 || $c >= $size) {
                        $fits = false;
                        break;
                    }
                    if ($grid[$r][$c] !== '' && $grid[$r][$c] !== $word[$i]) {
                        $fits = false;
                        break;
                    }
                    $cells[] = ['row' => $r, 'col' => $c];
                }

                if ($fits) {
                    // Place the word
                    foreach ($cells as $idx => $cell) {
                        $grid[$cell['row']][$cell['col']] = $word[$idx];
                    }
                    $wordPositions[] = [
                        'word'      => $word,
                        'start_row' => $row,
                        'start_col' => $col,
                        'direction' => [$dr, $dc],
                        'cells'     => $cells,
                    ];
                    $placed = true;
                }
            }
        }

        // Fill empty cells with random letters
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($grid[$r][$c] === '') {
                    $grid[$r][$c] = $letters[rand(0, 25)];
                }
            }
        }

        return [
            'grid'           => $grid,
            'word_positions' => $wordPositions,
        ];
    }

    protected function syncLegacyActivity(): void
    {
        $wordCount = count($this->words ?? []);
        $metadata  = array_merge($this->metadata ?? [], [
            'source'               => 'word_search_mirror',
            'legacy_word_search_id' => $this->id,
            'word_count'           => $wordCount,
            'grid_size'            => $this->grid_size,
        ]);

        $query = DB::table('activities')
            ->where('type', 'word_search')
            ->where(function ($q): void {
                $q->where('metadata->legacy_word_search_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'word_search',
            'title'        => $this->title,
            'description'  => $this->description,
            'age_range'    => $this->age_range !== 'All' ? $this->age_range : null,
            'star_points'  => $this->star_points,
            'metadata'     => json_encode($metadata),
            'is_published' => $this->status === 'published',
            'updated_at'   => now(),
        ];

        $existing = $query->orderByDesc('id')->first();
        if ($existing) {
            DB::table('activities')->where('id', $existing->id)->update($payload);
            return;
        }

        $payload['created_at'] = now();
        DB::table('activities')->insert($payload);
    }
}
