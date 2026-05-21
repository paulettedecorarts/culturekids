<?php

namespace App\Livewire\CMS\SpotDifferences;

use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\UsesPortalContext;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use App\Models\SpotDifference;
use App\Models\Tribe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SpotDifferenceEditor extends Component
{
    use LogsFileUploads, UsesPortalContext, ValidatesOnlyChangedOnEdit, WithFileUploads;

    public ?SpotDifference $activity = null;
    public bool $isEdit = false;

    // Basic fields
    public $tribe_id         = '';
    public $title            = '';
    public $description      = '';
    public $scene_name       = '';
    public $difficulty_level = 'easy';
    public $age_min          = 3;
    public $age_max          = 12;
    public $star_points      = 10;
    public $status           = 'draft';
    public $cultural_note    = '';
    public $time_limit_seconds = '';
    public $total_differences  = 5;

    // File uploads
    public $image_a_file = null;
    public $image_b_file = null;

    // Difference zones (marked on the image)
    public array $zones = [];

    // New zone form
    public float $newZoneX      = 50.0;
    public float $newZoneY      = 50.0;
    public float $newZoneRadius = 5.0;
    public string $newZoneLabel = '';

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->activity = SpotDifference::with('zones')->findOrFail($id);
            $this->isEdit   = true;
            $this->loadData();
        } else {
            $this->tribe_id = Tribe::first()?->id ?? '';
        }
    }

    protected function loadData(): void
    {
        $a = $this->activity;
        $this->tribe_id          = $a->tribe_id;
        $this->title             = $a->title;
        $this->description       = $a->description;
        $this->scene_name        = $a->scene_name;
        $this->difficulty_level  = $a->difficulty_level;
        $this->age_min           = $a->age_min;
        $this->age_max           = $a->age_max;
        $this->star_points       = $a->star_points;
        $this->status            = $a->status;
        $this->cultural_note     = $a->cultural_note;
        $this->time_limit_seconds = $a->time_limit_seconds;
        $this->total_differences  = $a->total_differences;

        $this->zones = $a->zones->map(fn ($z) => [
            'id'             => $z->id,
            'x_percent'      => $z->x_percent,
            'y_percent'      => $z->y_percent,
            'radius_percent' => $z->radius_percent,
            'label'          => $z->label ?? '',
            'order_index'    => $z->order_index,
        ])->toArray();
    }

    #[Computed]
    public function tribes()
    {
        return Tribe::orderBy('name')->get();
    }

    public function addZone(): void
    {
        $this->zones[] = [
            'id'             => null,
            'x_percent'      => $this->newZoneX,
            'y_percent'      => $this->newZoneY,
            'radius_percent' => $this->newZoneRadius,
            'label'          => $this->newZoneLabel,
            'order_index'    => count($this->zones),
        ];
        $this->newZoneLabel = '';
        $this->total_differences = count($this->zones);
    }

    public function addZoneFromClick(float $x, float $y): void
    {
        $this->zones[] = [
            'id'             => null,
            'x_percent'      => round($x, 2),
            'y_percent'      => round($y, 2),
            'radius_percent' => $this->newZoneRadius,
            'label'          => $this->newZoneLabel,
            'order_index'    => count($this->zones),
        ];
        $this->newZoneLabel = '';
        $this->total_differences = count($this->zones);
    }

    public function removeZone(int $index): void
    {
        unset($this->zones[$index]);
        $this->zones = array_values($this->zones);
        foreach ($this->zones as $i => &$z) {
            $z['order_index'] = $i;
        }
        $this->total_differences = count($this->zones);
    }

    protected function rules(): array
    {
        return [
            'tribe_id'         => ['required', 'exists:tribes,id'],
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'scene_name'       => ['nullable', 'string', 'max:255'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'age_min'          => ['required', 'integer', 'min:1', 'max:18'],
            'age_max'          => ['required', 'integer', 'min:1', 'max:18', 'gte:age_min'],
            'star_points'      => ['required', 'integer', 'min:1', 'max:100'],
            'status'           => ['required', 'in:draft,published,archived'],
            'image_a_file'     => ['nullable', 'sometimes', 'image', 'max:10240'],
            'image_b_file'     => ['nullable', 'sometimes', 'image', 'max:10240'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        Log::info('SpotDifferenceEditor save', [
            'zones' => count($this->zones),
        ]);

        DB::transaction(function (): void {
            $activity = $this->activity ?? new SpotDifference;

            $activity->fill([
                'tribe_id'          => $this->tribe_id,
                'title'             => $this->title,
                'description'       => $this->description,
                'scene_name'        => $this->scene_name ?: null,
                'difficulty_level'  => $this->difficulty_level,
                'age_min'           => $this->age_min,
                'age_max'           => $this->age_max,
                'star_points'       => $this->star_points,
                'status'            => $this->status,
                'cultural_note'     => $this->cultural_note,
                'time_limit_seconds' => $this->time_limit_seconds ?: null,
                'total_differences'  => count($this->zones) ?: $this->total_differences,
            ]);

            $activity->save();

            // Handle image uploads
            foreach ([
                'image_a_file' => 'image_a_path',
                'image_b_file' => 'image_b_path',
            ] as $field => $column) {
                if ($this->$field) {
                    try {
                        $path = $this->$field->storeAs(
                            'spot-differences',
                            'sd_' . $activity->id . '_' . $column . '_' . time() . '.' . $this->$field->getClientOriginalExtension(),
                            'public'
                        );
                        $activity->$column = $path;
                    } catch (\Exception $e) {}
                }
            }

            $activity->save();

            // Sync zones
            $keptIds = collect($this->zones)->pluck('id')->filter()->all();
            $activity->zones()->whereNotIn('id', $keptIds)->delete();

            foreach ($this->zones as $i => $z) {
                $activity->zones()->updateOrCreate(
                    ['id' => $z['id'] ?? null],
                    [
                        'x_percent'      => $z['x_percent'],
                        'y_percent'      => $z['y_percent'],
                        'radius_percent' => $z['radius_percent'],
                        'label'          => $z['label'] ?: null,
                        'order_index'    => $i,
                    ]
                );
            }

            $this->activity = $activity;
        });

        session()->flash('message', $this->isEdit ? 'Activity updated!' : 'Activity created!');
        $this->redirectRoute($this->portalRouteName('spot-differences.show'), ['id' => $this->activity->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.spot-differences.spot-difference-editor', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
