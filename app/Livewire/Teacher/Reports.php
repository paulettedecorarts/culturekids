<?php

namespace App\Livewire\Teacher;

use App\Models\Classroom;
use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Models\ReadingProgress;
use App\Models\ProgressEvent;
use App\Support\ContentProgressType;
use App\Support\TeacherActiveClassroom;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Reports extends Component
{
    public $classroom;
    public $className = '';
    public $reportPeriod;
    public $subjectMetrics = [];
    public $studentPerformance = [];

    public function mount()
    {
        $user = auth()->user();
        
        // Get active classroom
        $this->classroom = TeacherActiveClassroom::activeClassroom($user);
        
        if (!$this->classroom) {
            $this->className = 'No Active Class';
            $this->reportPeriod = now()->format('F Y');
            return;
        }
        
        $this->className = $this->classroom->name;
        $this->reportPeriod = now()->format('F Y');
        
        // Calculate class-wide metrics
        $this->calculateClassMetrics();
        
        // Get individual student performance
        $this->loadStudentPerformance();
    }

    public function calculateClassMetrics()
    {
        $students = $this->classroom->children;
        $totalStudents = $students->count();
        
        if ($totalStudents === 0) {
            $this->subjectMetrics = [
                ['attainment' => '0%', 'label' => 'Stories Completed'],
                ['attainment' => '0%', 'label' => 'Activities Completed'],
                ['attainment' => '0%', 'label' => 'Average Engagement'],
            ];
            return;
        }
        
        // Calculate stories completion rate
        $totalStoriesCompleted = ReadingProgress::whereIn('user_id', $students->pluck('id'))
            ->where('reading_progress.status', 'completed')
            ->count();
        $totalStoriesStarted = ReadingProgress::whereIn('user_id', $students->pluck('id'))
            ->count();
        $storiesRate = $totalStoriesStarted > 0 ? round(($totalStoriesCompleted / $totalStoriesStarted) * 100) : 0;
        
        // Calculate activities completion rate
        $childProfileIds = ChildProfile::whereIn('user_id', $students->pluck('id'))->pluck('id');
        $totalActivities = ProgressEvent::whereIn('child_profile_id', $childProfileIds)->count();
        $activitiesRate = $totalActivities > 0 ? 100 : 0; // All progress events are completed
        
        // Calculate engagement (students with any activity)
        $activeStudents = $students->filter(function ($student) {
            $profileIds = ChildProfile::where('user_id', $student->id)->pluck('id');

            return ChildContentProgress::query()
                ->whereIn('child_profile_id', $profileIds)
                ->where('status', 'completed')
                ->exists();
        })->count();
        $engagementRate = round(($activeStudents / $totalStudents) * 100);
        
        $this->subjectMetrics = [
            ['attainment' => $storiesRate . '%', 'label' => 'Stories Completed'],
            ['attainment' => $activitiesRate . '%', 'label' => 'Activities Completed'],
            ['attainment' => $engagementRate . '%', 'label' => 'Student Engagement'],
        ];
    }

    public function loadStudentPerformance()
    {
        $students = $this->classroom->children()->orderBy('name')->get();
        
        $this->studentPerformance = $students->map(function ($student) {
            $profiles = ChildProfile::where('user_id', $student->id)->get();
            $profileIds = $profiles->pluck('id');

            $completedRows = ChildContentProgress::query()
                ->whereIn('child_profile_id', $profileIds)
                ->where('status', 'completed')
                ->get(['content_type', 'stars_earned']);

            $totalStars = (int) $profiles->sum('total_stars');
            $completedStories = $completedRows->where('content_type', ContentProgressType::STORY)->count();
            $completedSongs = $completedRows->where('content_type', ContentProgressType::SONG)->count();
            $completedActivities = $completedRows
                ->filter(fn ($row) => ! in_array($row->content_type, [ContentProgressType::STORY, ContentProgressType::SONG], true))
                ->count();
            $totalCompleted = $completedStories + $completedSongs + $completedActivities;
            
            // Calculate badges (simple milestone system)
            $badges = 0;
            if ($totalStars >= 10) $badges++;
            if ($totalStars >= 50) $badges++;
            if ($totalStars >= 100) $badges++;
            if ($totalCompleted >= 5) $badges++;
            if ($totalCompleted >= 10) $badges++;
            
            // Determine status based on engagement
            $status = 'Needs Help';
            if ($totalCompleted >= 10) {
                $status = 'Master';
            } elseif ($totalCompleted >= 5) {
                $status = 'Excel';
            } elseif ($totalCompleted >= 1) {
                $status = 'Pass';
            }
            
            // Calculate score (percentage based on stars)
            $maxPossibleStars = 100; // Arbitrary max for percentage calculation
            $score = min(100, round(($totalStars / $maxPossibleStars) * 100));
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'score' => $score . '%',
                'badges' => $badges,
                'status' => $status,
                'total_stars' => $totalStars,
                'completed_items' => $totalCompleted,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.teacher.reports')
            ->layout('layouts.teacher');
    }
}

