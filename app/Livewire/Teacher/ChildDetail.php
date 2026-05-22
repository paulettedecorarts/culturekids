<?php

namespace App\Livewire\Teacher;

use App\Models\ChildProfile;
use App\Models\User;
use App\Support\TeacherActiveClassroom;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class ChildDetail extends Component
{
    public int $childId;

    public bool $canEdit = false;

    public ?int $editingProfile = null;

    public string $editName = '';

    public string $editDob = '';

    public string $editAgeBand = '';

    public bool $creatingProfile = false;

    public string $newName = '';

    public string $newDob = '';

    public string $newAgeBand = 'simple';

    public string $activityFilter = 'all';

    public function mount(int $id): void
    {
        $this->childId = $id;
        $this->authorizeChildAccess();
        $this->canEdit = true;
    }

    public function startCreateProfile(): void
    {
        $child = $this->child();
        $this->creatingProfile = true;
        $this->newName = $child->name;
        $this->newDob = '';
        $this->newAgeBand = 'simple';
        $this->resetValidation();
    }

    public function cancelCreate(): void
    {
        $this->creatingProfile = false;
        $this->newName = '';
        $this->newDob = '';
        $this->newAgeBand = 'simple';
    }

    public function createProfile(): void
    {
        $this->validate([
            'newName' => 'required|string|max:255',
            'newDob' => 'required|date',
            'newAgeBand' => 'required|in:simple,guided,advanced,full',
        ]);

        ChildProfile::create([
            'user_id' => $this->childId,
            'name' => $this->newName,
            'dob' => $this->newDob,
            'age_band' => $this->newAgeBand,
            'total_stars' => 0,
        ]);

        $this->cancelCreate();
        session()->flash('message', __('Profile created successfully'));
    }

    public function startEditProfile(int $profileId): void
    {
        $profile = $this->childProfiles()->firstWhere('id', $profileId);
        if (! $profile) {
            return;
        }

        $this->editingProfile = $profileId;
        $this->editName = $profile->name;
        $this->editDob = $profile->dob instanceof \DateTimeInterface
            ? $profile->dob->format('Y-m-d')
            : (string) $profile->dob;
        $this->editAgeBand = $profile->age_band;
    }

    public function cancelEdit(): void
    {
        $this->editingProfile = null;
        $this->editName = '';
        $this->editDob = '';
        $this->editAgeBand = '';
    }

    public function saveProfile(): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editDob' => 'required|date',
            'editAgeBand' => 'required|in:simple,guided,advanced,full',
        ]);

        $profile = ChildProfile::query()->findOrFail($this->editingProfile);
        abort_unless((int) $profile->user_id === $this->childId, 403);

        $profile->update([
            'name' => $this->editName,
            'dob' => $this->editDob,
            'age_band' => $this->editAgeBand,
        ]);

        $this->cancelEdit();
        session()->flash('message', __('Profile updated successfully'));
    }

    public function setActivityFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'completed', 'in_progress'], true)) {
            return;
        }

        $this->activityFilter = $filter;
    }

    public function render()
    {
        $child = $this->child();

        return view('livewire.teacher.child-detail', [
            'child' => $child,
            'childProfiles' => $this->childProfiles(),
            'stories' => $this->stories(),
            'activities' => $this->activities($this->childProfiles()),
            'badges' => $this->badges($this->childProfiles()),
        ]);
    }

    private function child(): User
    {
        return User::query()->with('roles')->findOrFail($this->childId);
    }

    private function authorizeChildAccess(): void
    {
        $user = auth()->user();
        $activeClassroom = TeacherActiveClassroom::activeClassroom($user);

        abort_unless(
            $user
            && $activeClassroom
            && $activeClassroom->children()->where('users.id', $this->childId)->exists(),
            403,
            __('You do not have access to this child')
        );
    }

    /** @return Collection<int, ChildProfile> */
    private function childProfiles(): Collection
    {
        return ChildProfile::query()
            ->where('user_id', $this->childId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (ChildProfile $profile) {
                $activityStars = (int) ($profile->total_stars ?? 0);
                $storyStars = (int) \App\Models\ReadingProgress::query()
                    ->where('user_id', $profile->user_id)
                    ->where('reading_progress.status', 'completed')
                    ->join('comics', 'reading_progress.comic_id', '=', 'comics.id')
                    ->sum('comics.star_points');

                $profile->calculated_total_stars = $activityStars + $storyStars;

                return $profile;
            });
    }

    /** @return Collection<int, \App\Models\ReadingProgress> */
    private function stories(): Collection
    {
        $query = \App\Models\ReadingProgress::query()
            ->where('user_id', $this->childId)
            ->with('comic.tribe');

        if ($this->activityFilter === 'completed') {
            $query->where('reading_progress.status', 'completed');
        } elseif ($this->activityFilter === 'in_progress') {
            $query->where('reading_progress.status', 'in_progress');
        }

        return $query->orderByDesc('last_read_at')->get();
    }

    /**
     * @param  Collection<int, ChildProfile>  $childProfiles
     * @return Collection<int, \App\Models\ProgressEvent>
     */
    private function activities(Collection $childProfiles): Collection
    {
        if ($childProfiles->isEmpty()) {
            return collect();
        }

        return \App\Models\ProgressEvent::query()
            ->whereIn('child_profile_id', $childProfiles->pluck('id'))
            ->with('activity.tribe')
            ->orderByDesc('completed_at')
            ->get();
    }

    /**
     * @param  Collection<int, ChildProfile>  $childProfiles
     * @return list<array<string, mixed>>
     */
    private function badges(Collection $childProfiles): array
    {
        if ($childProfiles->isEmpty()) {
            return [];
        }

        $profile = $childProfiles->first();
        $totalStars = (int) ($profile->calculated_total_stars ?? 0);
        $completedStories = \App\Models\ReadingProgress::query()
            ->where('user_id', $this->childId)
            ->where('reading_progress.status', 'completed')
            ->count();
        $completedActivities = \App\Models\ProgressEvent::query()
            ->where('child_profile_id', $profile->id)
            ->count();

        return [
            [
                'id' => 'first_steps',
                'title' => 'First Steps',
                'description' => 'Complete your first story',
                'icon' => '👣',
                'target' => 1,
                'current' => $completedStories,
                'unlocked' => $completedStories >= 1,
                'type' => 'stories',
            ],
            [
                'id' => 'getting_started',
                'title' => 'Getting Started',
                'description' => 'Complete 10 stories',
                'icon' => '🌱',
                'target' => 10,
                'current' => $completedStories,
                'unlocked' => $completedStories >= 10,
                'type' => 'stories',
            ],
            [
                'id' => 'bronze_explorer',
                'title' => 'Bronze Explorer',
                'description' => 'Earn 100 stars',
                'icon' => '🥉',
                'target' => 100,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 100,
                'type' => 'stars',
            ],
            [
                'id' => 'silver_learner',
                'title' => 'Silver Learner',
                'description' => 'Earn 500 stars',
                'icon' => '🥈',
                'target' => 500,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 500,
                'type' => 'stars',
            ],
            [
                'id' => 'gold_hero',
                'title' => 'Gold Hero',
                'description' => 'Earn 1,000 stars',
                'icon' => '🥇',
                'target' => 1000,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 1000,
                'type' => 'stars',
            ],
        ];
    }
}
