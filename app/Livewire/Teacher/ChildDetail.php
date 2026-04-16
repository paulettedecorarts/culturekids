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
