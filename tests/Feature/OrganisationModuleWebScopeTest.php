<?php

namespace Tests\Feature;

use App\Livewire\CMS\ApprovedContent;
use App\Livewire\CMS\ReviewQueue;
use App\Models\Activity;
use App\Models\Comic;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\OrganisationContentDecision;
use App\Models\Tribe;
use App\Models\User;
use App\Services\OrganisationContentReviewService;
use App\Services\TeacherApprovedCatalogService;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrganisationModuleWebScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModuleSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_review_queue_hides_pending_items_for_disabled_modules(): void
    {
        $org = $this->createOrg();
        $this->disableModule($org, 'stories');

        $tribe = Tribe::create([
            'name' => 'Review Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Pending Story',
            'status' => 'review',
            'age_min' => 2,
            'age_max' => 6,
            'star_points' => 10,
        ]);

        Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Pending Puzzle',
            'is_published' => true,
            'star_points' => 5,
        ]);

        $admin = $this->createOrgAdmin($org);

        Livewire::actingAs($admin)
            ->test(ReviewQueue::class)
            ->assertDontSee('Pending Story')
            ->assertSee('Pending Puzzle');
    }

    public function test_approved_content_list_hides_items_for_disabled_modules(): void
    {
        $org = $this->createOrg();
        $this->disableModule($org, 'puzzles');

        $tribe = Tribe::create([
            'name' => 'Approved Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        $puzzle = Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Approved Puzzle Item',
            'is_published' => true,
            'star_points' => 5,
        ]);

        $flashcard = Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'flashcard',
            'title' => 'Approved Flashcard Item',
            'is_published' => true,
            'star_points' => 5,
        ]);

        OrganisationContentDecision::create([
            'organisation_id' => $org->id,
            'content_type' => OrganisationContentDecision::TYPE_PUZZLE,
            'content_id' => $puzzle->id,
            'decision' => OrganisationContentDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        OrganisationContentDecision::create([
            'organisation_id' => $org->id,
            'content_type' => OrganisationContentDecision::TYPE_FLASHCARD,
            'content_id' => $flashcard->id,
            'decision' => OrganisationContentDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $admin = $this->createOrgAdmin($org);

        Livewire::actingAs($admin)
            ->test(ApprovedContent::class)
            ->assertDontSee('Approved Puzzle Item')
            ->assertSee('Approved Flashcard Item');
    }

    public function test_approve_returns_null_when_module_disabled(): void
    {
        $org = $this->createOrg();
        $this->disableModule($org, 'stories');

        $tribe = Tribe::create([
            'name' => 'Approve Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        $comic = Comic::create([
            'tribe_id' => $tribe->id,
            'title' => 'Blocked Story',
            'status' => 'review',
            'age_min' => 2,
            'age_max' => 6,
            'star_points' => 10,
        ]);

        $service = app(OrganisationContentReviewService::class);
        $result = $service->approve($org->id, 1, OrganisationContentDecision::TYPE_STORY, $comic->id);

        $this->assertNull($result);
    }

    public function test_teacher_catalog_excludes_stories_when_module_disabled(): void
    {
        $org = $this->createOrg();
        $this->disableModule($org, 'stories');

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $teacher->assignRole('teacher');

        $types = collect(app(TeacherApprovedCatalogService::class)->itemsFor($teacher))
            ->pluck('content_type');

        $this->assertNotContains(OrganisationContentDecision::TYPE_STORY, $types);
    }

    private function createOrg(): Organisation
    {
        return Organisation::create([
            'name' => 'Web Scope School',
            'code' => 'web-scope-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
    }

    private function createOrgAdmin(Organisation $org): User
    {
        $user = User::factory()->create(['organisation_id' => $org->id]);
        Role::firstOrCreate(['name' => 'org_admin', 'guard_name' => 'web']);
        $user->assignRole('org_admin');

        return $user;
    }

    private function disableModule(Organisation $org, string $moduleKey): void
    {
        $module = Module::query()->where('key', $moduleKey)->firstOrFail();
        $org->modules()->sync([
            $module->id => ['is_enabled' => false],
        ]);
    }
}
