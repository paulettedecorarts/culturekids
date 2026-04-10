<?php

namespace Tests\Feature\Teacher;

use App\Models\Comic;
use App\Models\Organisation;
use App\Models\OrganisationComicDecision;
use App\Models\Tribe;
use App\Models\User;
use App\Support\TeacherCatalogScope;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherCatalogScopeTest extends TestCase
{
    use RefreshDatabase;

    private function tribe(string $name): Tribe
    {
        return Tribe::create([
            'name' => $name.'-'.uniqid(),
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);
    }

    private function comic(Tribe $tribe, array $overrides = []): Comic
    {
        return Comic::create(array_merge([
            'org_id' => null,
            'tribe_id' => $tribe->id,
            'title' => 'Story',
            'description' => null,
            'age_min' => 2,
            'age_max' => 4,
            'status' => 'published',
        ], $overrides));
    }

    public function test_tribes_query_lists_only_tribes_from_approved_comics(): void
    {
        $this->seed(RoleSeeder::class);

        $tribeA = $this->tribe('A');
        $tribeB = $this->tribe('B');

        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $comicA = $this->comic($tribeA, ['title' => 'On A']);
        $this->comic($tribeB, ['title' => 'On B']);

        OrganisationComicDecision::create([
            'organisation_id' => $org->id,
            'comic_id' => $comicA->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $ids = TeacherCatalogScope::tribesQueryFor($teacher)->pluck('id')->all();

        $this->assertSame([$tribeA->id], $ids);
    }

    public function test_comics_query_lists_pivot_approved_and_school_owned(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe('T');
        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $other = Organisation::create([
            'name' => 'Other',
            'code' => 'other-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $global = $this->comic($t, ['title' => 'Global']);
        $mine = $this->comic($t, ['title' => 'Mine', 'org_id' => $org->id]);
        $theirs = $this->comic($t, ['title' => 'Theirs', 'org_id' => $other->id]);

        OrganisationComicDecision::create([
            'organisation_id' => $org->id,
            'comic_id' => $global->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $ids = TeacherCatalogScope::comicsQueryFor($teacher)->pluck('id')->sort()->values()->all();

        $this->assertEqualsCanonicalizing([$global->id, $mine->id], $ids);
        $this->assertNotContains($theirs->id, $ids);
    }

    public function test_published_shared_comics_without_pivot_are_hidden(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe('T');
        $this->comic($t, ['title' => 'Shared no pivot']);

        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $this->assertSame([], TeacherCatalogScope::comicsQueryFor($teacher)->pluck('id')->all());
    }

    public function test_user_can_view_comic_matches_pivot_and_school_owned_rules(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe('T');
        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $published = $this->comic($t, ['status' => 'published']);
        $draft = $this->comic($t, ['title' => 'Draft', 'status' => 'draft']);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $this->assertFalse(TeacherCatalogScope::userCanViewComic($teacher, $published));

        OrganisationComicDecision::create([
            'organisation_id' => $org->id,
            'comic_id' => $published->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => $admin->id,
        ]);

        $this->assertTrue(TeacherCatalogScope::userCanViewComic($teacher, $published));
        $this->assertFalse(TeacherCatalogScope::userCanViewComic($teacher, $draft));
    }

    public function test_other_organisation_pivot_does_not_grant_access(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe('T');
        $orgA = Organisation::create([
            'name' => 'School A',
            'code' => 'a-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);
        $orgB = Organisation::create([
            'name' => 'School B',
            'code' => 'b-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $comic = $this->comic($t, ['title' => 'Shared title']);
        OrganisationComicDecision::create([
            'organisation_id' => $orgB->id,
            'comic_id' => $comic->id,
            'decision' => OrganisationComicDecision::DECISION_APPROVED,
            'decided_by' => null,
        ]);

        $teacherA = User::factory()->create(['organisation_id' => $orgA->id]);
        $teacherA->assignRole('teacher');

        $this->assertSame([], TeacherCatalogScope::comicsQueryFor($teacherA)->pluck('id')->all());
        $this->assertFalse(TeacherCatalogScope::userCanViewComic($teacherA, $comic));
    }
}
