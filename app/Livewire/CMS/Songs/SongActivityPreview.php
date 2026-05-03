<?php

namespace App\Livewire\CMS\Songs;

use App\Models\Song;
use App\Models\SongActivity;
use Livewire\Component;

class SongActivityPreview extends Component
{
    public Song $song;
    public string $mode = 'karaoke'; // karaoke, fill_blanks, lullaby
    public float $currentTime = 0;
    public bool $isPlaying = false;
    public array $userAnswers = [];
    public int $starsEarned = 0;
    public bool $completed = false;
    public ?int $activeSegmentIndex = null;

    public function mount(int $id): void
    {
        $this->song = Song::with(['lyricSegments' => function($query) {
            $query->orderBy('order_index');
        }])->findOrFail($id);
        
        // Set default mode based on song activity type
        $this->mode = $this->song->activity_type ?? 'karaoke';
        
        // Initialize user answers for fill-the-blanks mode
        if ($this->mode === 'fill_blanks') {
            foreach ($this->song->lyricSegments->where('is_fill_blank', true) as $segment) {
                $this->userAnswers[$segment->id] = '';
            }
        }
    }

    public function switchMode(string $mode): void
    {
        $this->mode = $mode;
        $this->resetActivity();
    }

    public function resetActivity(): void
    {
        $this->currentTime = 0;
        $this->isPlaying = false;
        $this->starsEarned = 0;
        $this->completed = false;
        $this->activeSegmentIndex = null;
        
        if ($this->mode === 'fill_blanks') {
            foreach ($this->song->lyricSegments->where('is_fill_blank', true) as $segment) {
                $this->userAnswers[$segment->id] = '';
            }
        }
    }

    public function checkAnswers(): void
    {
        if ($this->mode !== 'fill_blanks') return;
        
        $correctAnswers = 0;
        $totalBlanks = 0;
        
        foreach ($this->song->lyricSegments->where('is_fill_blank', true) as $segment) {
            $totalBlanks++;
            $userAnswer = trim(strtolower($this->userAnswers[$segment->id] ?? ''));
            $correctAnswer = trim(strtolower($segment->blank_answer));
            
            if ($userAnswer === $correctAnswer) {
                $correctAnswers++;
            }
        }
        
        if ($totalBlanks > 0) {
            $percentage = ($correctAnswers / $totalBlanks) * 100;
            $this->starsEarned = match(true) {
                $percentage >= 90 => 5,
                $percentage >= 75 => 4,
                $percentage >= 60 => 3,
                $percentage >= 40 => 2,
                $percentage >= 20 => 1,
                default => 0,
            };
            
            if ($percentage >= 60) {
                $this->completed = true;
            }
        }
    }

    public function simulateCompletion(): void
    {
        $this->completed = true;
        $this->starsEarned = rand(3, 5); // Simulate earning 3-5 stars
    }

    public function getActiveSegment()
    {
        if (!$this->song->lyricSegments->count()) return null;
        
        foreach ($this->song->lyricSegments as $index => $segment) {
            if ($this->currentTime >= $segment->start_time && $this->currentTime <= $segment->end_time) {
                $this->activeSegmentIndex = $index;
                return $segment;
            }
        }
        
        return null;
    }

    public function render()
    {
        return view('livewire.cms.songs.song-activity-preview')
            ->layout('layouts.preview'); // Use a clean preview layout
    }
}