<?php

namespace App\Livewire\CMS\Mazes;

use App\Livewire\Concerns\CoercesNumericFormFields;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Jobs\SyncMazeLegacyActivity;
use App\Models\Maze;
use App\Models\Tribe;
use App\Support\MazePlayableGrid;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class MazeEditor extends Component
{
    use CoercesNumericFormFields;
    use LogsFileUploads, UsesPortalContext, ValidatesOnlyChangedOnEdit, WithFileUploads;

    public ?Maze $maze = null;

    public bool $isEdit = false;

    // Basic fields
    public $tribe_id = '';

    public $title = '';

    public $description = '';

    public $maze_type = 'standard';

    public $difficulty_level = 'easy';

    public $age_min = 3;

    public $age_max = 12;

    public $star_points = 10;

    public $status = 'draft';

    public $cultural_note = '';

    public $hero_character = '';

    // Grid settings
    public int $grid_rows = 10;

    public int $grid_cols = 10;

    public array $grid = [];

    // Positions
    public array $start_position = ['row' => 0, 'col' => 0];

    public array $end_position = ['row' => 9, 'col' => 9];

    // Collectibles
    public array $collectibles = [];

    public string $collectibleEmoji = '💎';

    public string $collectibleLabel = '';

    public bool $collectibleRequired = true;

    public int $collectibleRow = 0;

    public int $collectibleCol = 0;

    // Type-specific
    public $time_limit_seconds = '';

    public $visibility_radius = 3;

    /** @var array<string, mixed> */
    public array $metadata = [];

    // File uploads
    public $cover_image_file = null;

    public $background_image_file = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->maze = Maze::findOrFail($id);
            $this->isEdit = true;
            $this->loadMazeData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
            $this->initGrid();
        }
    }

    protected function loadMazeData(): void
    {
        $m = $this->maze;
        $this->tribe_id = $m->tribe_id;
        $this->title = $m->title;
        $this->description = $m->description;
        $this->maze_type = $m->maze_type;
        $this->difficulty_level = $m->difficulty_level;
        $this->age_min = $m->age_min;
        $this->age_max = $m->age_max;
        $this->star_points = $m->star_points;
        $this->status = $m->status;
        $this->cultural_note = $m->cultural_note;
        $this->hero_character = $m->hero_character;
        $this->grid_rows = $m->grid_rows;
        $this->grid_cols = $m->grid_cols;
        $this->grid = $m->grid ?? [];
        $this->start_position = $m->start_position ?? ['row' => 0, 'col' => 0];
        $this->end_position = $m->end_position ?? ['row' => $m->grid_rows - 1, 'col' => $m->grid_cols - 1];
        $this->collectibles = $m->collectibles ?? [];
        $this->time_limit_seconds = $m->time_limit_seconds;
        $this->visibility_radius = $m->visibility_radius ?? 3;
        $this->metadata = is_array($m->metadata) ? $m->metadata : [];

        if (empty($this->grid)) {
            $this->initGrid();
        } else {
            $this->normalizeGridDimensions();
        }
    }

    protected function normalizeGridDimensions(): void
    {
        $rows = max(5, min(20, (int) $this->grid_rows));
        $cols = max(5, min(20, (int) $this->grid_cols));
        $this->grid_rows = $rows;
        $this->grid_cols = $cols;

        $normalized = [];
        for ($r = 0; $r < $rows; $r++) {
            $normalized[$r] = [];
            for ($c = 0; $c < $cols; $c++) {
                $normalized[$r][$c] = (int) ($this->grid[$r][$c] ?? 1);
            }
        }
        $this->grid = $normalized;
    }

    protected function initGrid(): void
    {
        $this->grid = [];
        for ($r = 0; $r < $this->grid_rows; $r++) {
            $this->grid[$r] = [];
            for ($c = 0; $c < $this->grid_cols; $c++) {
                // Border = wall, interior = path
                $this->grid[$r][$c] = ($r === 0 || $r === $this->grid_rows - 1 || $c === 0 || $c === $this->grid_cols - 1) ? 1 : 0;
            }
        }
        $this->start_position = ['row' => 0, 'col' => 1];
        $this->end_position = ['row' => $this->grid_rows - 1, 'col' => $this->grid_cols - 2];
    }

    // Grid interaction mode
    public string $gridMode = 'toggle'; // toggle | start | end

    public function setGridMode(string $mode): void
    {
        $this->gridMode = $mode;
    }

    public function resizeGrid(): void
    {
        $this->grid_rows = max(5, min(20, (int) $this->grid_rows));
        $this->grid_cols = max(5, min(20, (int) $this->grid_cols));
        $this->initGrid();
    }

    public function handleCellClick(int $row, int $col): void
    {
        if ($this->gridMode === 'start') {
            $this->grid[$row][$col] = 0;
            $this->start_position = ['row' => $row, 'col' => $col];
        } elseif ($this->gridMode === 'end') {
            $this->grid[$row][$col] = 0;
            $this->end_position = ['row' => $row, 'col' => $col];
        } else {
            // toggle mode — don't toggle start/end cells
            if (($row === $this->start_position['row'] && $col === $this->start_position['col']) ||
                ($row === $this->end_position['row'] && $col === $this->end_position['col'])) {
                return;
            }
            $this->grid[$row][$col] = ($this->grid[$row][$col] ?? 0) ? 0 : 1;
        }
    }

    public function fillAllWalls(): void
    {
        for ($r = 0; $r < $this->grid_rows; $r++) {
            for ($c = 0; $c < $this->grid_cols; $c++) {
                $this->grid[$r][$c] = 1;
            }
        }
    }

    public function clearAllWalls(): void
    {
        for ($r = 0; $r < $this->grid_rows; $r++) {
            for ($c = 0; $c < $this->grid_cols; $c++) {
                $this->grid[$r][$c] = 0;
            }
        }
    }

    public function wallCount(): int
    {
        $count = 0;
        foreach ($this->grid as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($row as $cell) {
                if ((int) $cell === 1) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function addCollectible(): void
    {
        $this->collectibles[] = [
            'row' => $this->collectibleRow,
            'col' => $this->collectibleCol,
            'emoji' => $this->collectibleEmoji,
            'label' => $this->collectibleLabel,
            'required' => $this->collectibleRequired,
        ];
        $this->collectibleLabel = '';
    }

    public function removeCollectible(int $index): void
    {
        unset($this->collectibles[$index]);
        $this->collectibles = array_values($this->collectibles);
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    protected function rules(): array
    {
        return [
            'tribe_id' => ['required', 'exists:tribes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'maze_type' => ['required', 'in:standard,timed,collect_items,visibility,reverse,circular'],
            'difficulty_level' => ['required', 'in:easy,medium,hard,expert,master'],
            'age_min' => ['required', 'integer', 'min:1', 'max:18'],
            'age_max' => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:draft,published,archived'],
            'grid_rows' => ['required', 'integer', 'min:5', 'max:20'],
            'grid_cols' => ['required', 'integer', 'min:5', 'max:20'],
            'cover_image_file' => ['nullable', 'sometimes', 'image', 'max:5120'],
            'background_image_file' => ['nullable', 'sometimes', 'image', 'max:10240'],
        ];
    }

    protected function ensureStartEndOnPath(): void
    {
        MazePlayableGrid::applyMarkersToGrid($this->grid, $this->start_position, $this->end_position);
    }

    public function save(): void
    {
        $this->validate();
        $this->ensureStartEndOnPath();

        Log::info('MazeEditor save', [
            'type' => $this->maze_type,
            'grid' => $this->grid_rows.'x'.$this->grid_cols,
        ]);

        $maze = $this->maze ?? new Maze;

        $maze->fill([
            'tribe_id' => $this->tribe_id,
            'title' => $this->title,
            'description' => $this->description,
            'maze_type' => $this->maze_type,
            'difficulty_level' => $this->difficulty_level,
            'age_min' => $this->age_min,
            'age_max' => $this->age_max,
            'star_points' => $this->star_points,
            'status' => $this->status,
            'cultural_note' => $this->cultural_note,
            'hero_character' => $this->hero_character ?: null,
            'grid' => $this->grid,
            'grid_rows' => $this->grid_rows,
            'grid_cols' => $this->grid_cols,
            'start_position' => $this->start_position,
            'end_position' => $this->end_position,
            'collectibles' => $this->collectibles ?: null,
            'time_limit_seconds' => $this->time_limit_seconds ?: null,
                'visibility_radius' => in_array($this->maze_type, ['visibility']) ? $this->visibility_radius : null,
                'metadata' => $this->metadata ?: null,
            ]);

        if (! $maze->exists) {
            $maze->saveQuietly();
        }

        foreach ([
            'cover_image_file' => ['games/maze-covers', 'cover_image_path'],
            'background_image_file' => ['games/maze-backgrounds', 'background_image_path'],
        ] as $field => [$dir, $column]) {
            if ($this->$field) {
                try {
                    $path = $this->$field->storeAs(
                        $dir,
                        'maze_'.$maze->id.'_'.time().'.'.$this->$field->getClientOriginalExtension(),
                        'public'
                    );
                    $maze->$column = $path;
                } catch (\Exception $e) {
                }
            }
        }

        $maze->saveQuietly();
        $this->maze = $maze;

        SyncMazeLegacyActivity::dispatch((int) $this->maze->id)->afterResponse();

        session()->flash('message', $this->isEdit ? 'Maze updated!' : 'Maze created!');
        $this->redirectRoute($this->portalRouteName('mazes.show'), ['id' => $this->maze->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.mazes.maze-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
            'mazeTypes' => Maze::TYPES,
            'difficulties' => Maze::DIFFICULTIES,
        ])->layout($this->portalLayout());
    }
}
