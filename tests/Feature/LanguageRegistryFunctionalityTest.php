<?php

namespace Tests\Feature;

use App\Livewire\Admin\LanguageDetailPage;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LanguageRegistryFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_languages_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/languages')->assertUnauthorized();
    }

    public function test_super_admin_can_fetch_languages_registry_api(): void
    {
        $user = $this->createSuperAdminUser();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/languages');

        $response->assertOk()
            ->assertJsonCount(2, 'languages')
            ->assertJsonPath('languages.0.code', 'lug-UG');
    }

    public function test_language_detail_page_supports_create_edit_delete(): void
    {
        $this->actingAs($this->createSuperAdminUser());

        Livewire::test(LanguageDetailPage::class)
            ->set('name', 'Runyankole')
            ->set('native_name', 'Runyankore')
            ->set('code', 'nyn-UG')
            ->set('flag_emoji', '🇺🇬')
            ->set('audio_pack_available', true)
            ->set('is_active', true)
            ->set('sort_order', 30)
            ->set('notes', 'Initial onboarding set.')
            ->call('saveLanguage')
            ->assertHasNoErrors();

        $created = Language::where('code', 'nyn-UG')->firstOrFail();
        $this->assertDatabaseHas('languages', [
            'id' => $created->id,
            'name' => 'Runyankole',
            'translation_coverage' => 0,
            'status' => 'pending',
        ]);

        Livewire::test(LanguageDetailPage::class, ['id' => $created->id])
            ->call('startEditing')
            ->set('name', 'Runyankole (updated)')
            ->call('saveLanguage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('languages', ['id' => $created->id, 'name' => 'Runyankole (updated)']);

        Livewire::test(LanguageDetailPage::class, ['id' => $created->id])
            ->call('deleteLanguage')
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('languages', ['id' => $created->id]);
    }

    private function createSuperAdminUser(): User
    {
        $user = User::factory()->create();
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $user->assignRole('super_admin');

        return $user;
    }
}
