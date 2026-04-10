<?php

namespace Tests\Feature\Teacher;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\Organisation;
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

    private function approveComic(User $orgAdmin, Comic $comic): void
    {
        AuditLog::create([
            'user_id' => $orgAdmin->id,
            'action' => 'APPROVE_COMIC',
            'resource' => 'comics/'.$comic->id,
            'status' => 'success',
        ]);
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

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $comicA = $this->comic($tribeA, ['title' => 'On A']);
        $this->comic($tribeB, ['title' => 'On B']);

        $this->approveComic($admin, $comicA);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $ids = TeacherCatalogScope::tribesQueryFor($teacher)->pluck('id')->all();

        $this->assertSame([$tribeA->id], $ids);
    }

    public function test_comics_query_lists_only_org_approved_published_comics(): void
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

        $admin = User::factory()->create(['organisation_id' => $org->id]);
        $admin->assignRole('org_admin');

        $global = $this->comic($t, ['title' => 'Global']);
        $mine = $this->comic($t, ['title' => 'Mine', 'org_id' => $org->id]);
        $theirs = $this->comic($t, ['title' => 'Theirs', 'org_id' => $other->id]);

        $this->approveComic($admin, $global);
        $this->approveComic($admin, $mine);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $ids = TeacherCatalogScope::comicsQueryFor($teacher)->pluck('id')->sort()->values()->all();

        $this->assertEqualsCanonicalizing([$global->id, $mine->id], $ids);
        $this->assertNotContains($theirs->id, $ids);
    }

    public function test_published_comics_without_review_queue_approval_are_hidden(): void
    {
        $this->seed(RoleSeeder::class);

        $t = $this->tribe('T');
        $this->comic($t, ['title' => 'Published but never approved for this org']);

        $org = Organisation::create([
            'name' => 'School',
            'code' => 'school-'.uniqid(),
            'plan' => 'school',
            'status' => 'active',
        ]);

        $teacher = User::factory()->create(['organisation_id' => $org->id]);
        $teacher->assignRole('teacher');

        $this->assertSame([], TeacherCatalogScope::comicsQueryFor($teacher)->pluck('id')->all());
        $this->assertSame([], TeacherCatalogScope::tribesQueryFor($teacher)->pluck('id')->all());
    }

    public function test_user_can_view_comic_requires_org_admin_approval(): void
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

        $this->approveComic($admin, $published);

        $this->assertTrue(TeacherCatalogScope::userCanViewComic($teacher, $published));
        $this->assertFalse(TeacherCatalogScope::userCanViewComic($teacher, $draft));
    }

    public function test_other_organisation_approvals_do_not_grant_access(): void
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

        $adminB = User::factory()->create(['organisation_id' => $orgB->id]);
        $adminB->assignRole('org_admin');

        $comic = $this->comic($t, ['title' => 'Shared title']);
        $this->approveComic($adminB, $comic);

        $teacherA = User::factory()->create(['organisation_id' => $orgA->id]);
        $teacherA->assignRole('teacher');

        $this->assertSame([], TeacherCatalogScope::comicsQueryFor($teacherA)->pluck('id')->all());
        $this->assertFalse(TeacherCatalogScope::userCanViewComic($teacherA, $comic));
    }
}
