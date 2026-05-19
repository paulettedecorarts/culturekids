<?php

namespace Tests\Feature\Api;

use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\PanelVocabTag;
use App\Models\Tribe;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ComicVocabTagsApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
    }

    public function test_comic_show_includes_panel_vocab_tags(): void
    {
        $tribe = Tribe::create([
            'name' => 'Baganda',
            'hero_name' => 'Gipir',
            'region' => 'Central',
        ]);

        $comic = Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Water Story',
            'status' => 'published',
            'age_min' => 3,
            'age_max' => 6,
            'star_points' => 10,
        ]);

        $panel = ComicPanel::create([
            'comic_id' => $comic->id,
            'order_index' => 0,
            'image_path' => 'comics/panels/test.jpg',
            'caption' => 'Look at the river',
        ]);

        PanelVocabTag::create([
            'panel_id' => $panel->id,
            'word' => 'PIJ',
            'translation' => 'Water',
            'phonetic' => 'pee-j',
            'x_position' => 10,
            'y_position' => 20,
            'width' => 80,
            'height' => 40,
        ]);

        $user = User::factory()->create(['organisation_id' => null]);
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $user->assignRole('parent');
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/comics/{$comic->id}")->assertOk();

        $response->assertJsonPath('panels.0.text', 'Look at the river');
        $response->assertJsonPath('panels.0.vocab_tags.0.word', 'PIJ');
        $response->assertJsonPath('panels.0.vocab_tags.0.translation', 'Water');
        $response->assertJsonPath('panels.0.vocab_tags.0.phonetic', 'pee-j');
        $response->assertJsonPath('panels.0.vocab_tags.0.x_position', 10);
    }
}
