<?php

namespace App\Livewire\Teacher;

use App\Models\User;
use App\Models\ChildProfile;
use App\Support\TeacherActiveClassroom;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Carbon\Carbon;

#[Layout('layouts.teacher')]
class ChildDetail extends Component
{
    public $childId;
    public $child;
    public $childProfiles = [];
    public $canEdit = false;
    
    // Editable fields
    public $editingProfile = null;
    public $editName = '';
    public $editDob = '';
    public $editAgeBand = '';
    
    // Create new profile
    public $creatingProfile = false;
    public $newName = '';
    public $newDob = '';
    public $newAgeBand = 'simple';
    
    // Activity history
    public $activityFilter = 'all'; // all, completed, in_progress
    public $stories = [];
    public $activities = [];
    public $badges = [];

    public function mount($id)
    {
        $this->childId = $id;
        $this->loadChild();
    }

    public function loadChild()
    {
        $user = auth()->user();
        
        // Get the child user
        $this->child = User::with('roles')->findOrFail($this->childId);
        
        // Verify teacher has access to this child through their classroom
        $activeClassroom = TeacherActiveClassroom::activeClassroom($user);
        
        if (!$activeClassroom || !$activeClassroom->children()->where('users.id', $this->childId)->exists()) {
            abort(403, 'You do not have access to this child');
        }
        
        // Load child profiles with calculated total stars
        $this->childProfiles = ChildProfile::where('user_id', $this->childId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($profile) {
                // Calculate stars from activities (child_profile level)
                $activityStars = $profile->total_stars ?? 0;
                
                // Calculate stars from completed stories (user level)
                $storyStars = \App\Models\ReadingProgress::where('user_id', $profile->user_id)
                    ->where('reading_progress.status', 'completed')
                    ->join('comics', 'reading_progress.comic_id', '=', 'comics.id')
                    ->sum('comics.star_points');
                
                // Add calculated total
                $profile->calculated_total_stars = $activityStars + $storyStars;
                
                return $profile;
            });
        
        $this->canEdit = true;
        
        // Load activity history
        $this->loadActivityHistory();
        
        // Load badges
        $this->loadBadges();
    }
    
    public function loadActivityHistory()
    {
        // Get stories/comics progress
        $storiesQuery = \App\Models\ReadingProgress::where('user_id', $this->childId)
            ->with('comic.tribe');
        
        if ($this->activityFilter === 'completed') {
            $storiesQuery->where('reading_progress.status', 'completed');
        } elseif ($this->activityFilter === 'in_progress') {
            $storiesQuery->where('reading_progress.status', 'in_progress');
        }
        
        $this->stories = $storiesQuery->orderBy('last_read_at', 'desc')->get();
        
        // Get activities (flashcards, puzzles) - only completed ones are tracked
        if ($this->childProfiles->isNotEmpty()) {
            $childProfileIds = $this->childProfiles->pluck('id');
            
            $activitiesQuery = \App\Models\ProgressEvent::whereIn('child_profile_id', $childProfileIds)
                ->with('activity.tribe');
            
            $this->activities = $activitiesQuery->orderBy('completed_at', 'desc')->get();
        }
    }
    
    public function loadBadges()
    {
        if ($this->childProfiles->isEmpty()) {
            $this->badges = [];
            return;
        }
        
        $profile = $this->childProfiles->first();
        
        // Calculate total stars and completed counts
        $totalStars = $profile->calculated_total_stars ?? 0;
        $completedStories = \App\Models\ReadingProgress::where('user_id', $this->childId)
            ->where('reading_progress.status', 'completed')
            ->count();
        $completedActivities = \App\Models\ProgressEvent::where('child_profile_id', $profile->id)->count();
        
        // Define milestone badges
        $this->badges = [
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
    
    public function setActivityFilter($filter)
    {
        $this->activityFilter = $filter;
        $this->loadActivityHistory();
    }

    public function startEditProfile($profileId)
    {
        $profile = $this->childProfiles->firstWhere('id', $profileId);
        if ($profile) {
            $this->editingProfile = $profileId;
            $this->editName = $profile->name;
            $this->editDob = $profile->dob;
            $this->editAgeBand = $profile->age_band;
        }
    }

    public function cancelEdit()
    {
        $this->editingProfile = null;
        $this->editName = '';
        $this->editDob = '';
        $this->editAgeBand = '';
    }

    public function saveProfile()
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editDob' => 'required|date',
            'editAgeBand' => 'required|in:simple,guided,advanced,full',
        ]);

        $profile = ChildProfile::findOrFail($this->editingProfile);
        $profile->update([
            'name' => $this->editName,
            'dob' => $this->editDob,
            'age_band' => $this->editAgeBand,
        ]);

        $this->cancelEdit();
        $this->loadChild();
        
        session()->flash('message', 'Profile updated successfully');
    }

    public function startCreateProfile()
    {
        $this->creatingProfile = true;
        $this->newName = $this->child->name; // Pre-fill with user's name
        $this->newDob = '';
        $this->newAgeBand = 'simple';
    }

    public function cancelCreate()
    {
        $this->creatingProfile = false;
        $this->newName = '';
        $this->newDob = '';
        $this->newAgeBand = 'simple';
    }

    public function createProfile()
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
        $this->loadChild();
        
        session()->flash('message', 'Profile created successfully');
    }

    public function render()
    {
        return view('livewire.teacher.child-detail', [
            'child' => $this->child,
            'childProfiles' => $this->childProfiles,
        ]);
    }
}
