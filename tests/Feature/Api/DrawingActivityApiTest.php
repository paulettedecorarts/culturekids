<?php

namespace Tests\Feature\Api;

use App\Models\Activity;
use App\Models\Drawing;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DrawingActivityApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_colouring_activity_show_includes_drawing_payload(): void
    {
        $tribe = Tribe::create([
            'name' => 'Baganda',
            'hero_name' => 'Kintu',
            'region' => 'Central',
        ]);

        $drawing = Drawing::create([
            'tribe_id' => $tribe->id,
            'title' => 'Totem Colouring',
            'description' => 'Colour the clan totem.',
            'drawing_type' => 'coloring',
            'difficulty_level' => 'easy',
            'age_min' => 4,
            'age_max' => 8,
            'star_points' => 8,
            'status' => 'published',
            'template_path' => 'drawings/templates/totem.png',
            'preview_path' => 'drawings/previews/totem.jpg',
            'color_palette' => ['#FF0000', '#00FF00', '#0000FF'],
        ]);

        $activity = Activity::query()
            ->where('type', 'drawing_kit')
            ->where('metadata->legacy_drawing_id', $drawing->id)
            ->first();

        $this->assertNotNull($activity);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson("/api/v1/activities/{$activity->id}")
            ->assertOk()
            ->assertJsonPath('type', 'drawing_kit')
            ->assertJsonPath('drawing_data.drawing.drawing_type', 'coloring')
            ->assertJsonPath('drawing_data.drawing.drawing_type_label', 'Colouring Page')
            ->assertJsonPath('drawing_data.drawing.color_palette.0', '#FF0000')
            ->assertJsonPath('cover_image', asset('storage/drawings/previews/totem.jpg'));
    }

    public function test_colouring_activity_list_returns_colouring_type(): void
    {
        $tribe = Tribe::create([
            'name' => 'Acholi',
            'hero_name' => 'Hero',
            'region' => 'North',
        ]);

        Drawing::create([
            'tribe_id' => $tribe->id,
            'title' => 'Drum Pattern',
            'description' => 'Colour the drum.',
            'drawing_type' => 'coloring',
            'difficulty_level' => 'easy',
            'age_min' => 4,
            'age_max' => 8,
            'star_points' => 10,
            'status' => 'published',
            'preview_path' => 'drawings/previews/drum.jpg',
        ]);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/content?type=colouring')
            ->assertOk()
            ->assertJsonPath('0.type', 'colouring')
            ->assertJsonPath('0.cover_image', asset('storage/drawings/previews/drum.jpg'));
    }
}
