<?php

namespace App\Livewire\Student;

use App\Models\Drawing;
use App\Models\DrawingSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class DrawingPlayer extends Component
{
    public Drawing $drawing;
    public ?DrawingSubmission $submission = null;
    
    // Drawing state
    public bool $isDrawing = false;
    public string $currentTool = 'brush';
    public string $currentColor = '#000000';
    public int $brushSize = 5;
    public array $drawingHistory = [];
    public int $historyIndex = -1;
    
    // UI state
    public bool $showColorPicker = false;
    public bool $showToolbar = true;
    public bool $completed = false;
    public int $starsEarned = 0;
    public int $timeSpent = 0;
    
    // Canvas data
    public ?string $canvasData = null;

    public function mount(int $id): void
    {
        $this->drawing = Drawing::with('tribe')->findOrFail($id);
        
        // Load or create submission
        $this->submission = DrawingSubmission::where('drawing_id', $this->drawing->id)
            ->where('user_id', Auth::id())
            ->first();
            
        if (!$this->submission) {
            $this->submission = DrawingSubmission::create([
                'drawing_id' => $this->drawing->id,
                'user_id' => Auth::id(),
                'artwork_path' => '',
                'started_at' => now(),
            ]);
        }
        
        // Load existing drawing data if available
        if ($this->submission->drawing_data) {
            $this->canvasData = $this->submission->drawing_data['canvas'] ?? null;
            $this->timeSpent = $this->submission->time_spent_seconds;
        }
        
        // Set default color from palette
        if (!empty($this->drawing->color_palette)) {
            $this->currentColor = $this->drawing->color_palette[0];
        }
    }

    public function selectTool(string $tool): void
    {
        $this->currentTool = $tool;
        $this->showColorPicker = false;
    }

    public function selectColor(string $color): void
    {
        $this->currentColor = $color;
        $this->showColorPicker = false;
    }

    public function setBrushSize(int $size): void
    {
        $this->brushSize = max(1, min(20, $size));
    }

    public function toggleColorPicker(): void
    {
        $this->showColorPicker = !$this->showColorPicker;
    }

    public function toggleToolbar(): void
    {
        $this->showToolbar = !$this->showToolbar;
    }

    public function clearCanvas(): void
    {
        $this->canvasData = null;
        $this->drawingHistory = [];
        $this->historyIndex = -1;
        $this->dispatch('clearCanvas');
    }

    public function undoAction(): void
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->dispatch('restoreFromHistory', $this->drawingHistory[$this->historyIndex]);
        }
    }

    public function redoAction(): void
    {
        if ($this->historyIndex < count($this->drawingHistory) - 1) {
            $this->historyIndex++;
            $this->dispatch('restoreFromHistory', $this->drawingHistory[$this->historyIndex]);
        }
    }

    public function saveProgress(string $canvasDataUrl, int $timeSpent): void
    {
        $this->canvasData = $canvasDataUrl;
        $this->timeSpent = $timeSpent;
        
        // Save to database
        $this->submission->update([
            'drawing_data' => [
                'canvas' => $canvasDataUrl,
                'tools_used' => $this->getToolsUsed(),
                'colors_used' => $this->getColorsUsed(),
            ],
            'time_spent_seconds' => $timeSpent,
        ]);
    }

    public function addToHistory(string $canvasDataUrl): void
    {
        // Remove any history after current index
        $this->drawingHistory = array_slice($this->drawingHistory, 0, $this->historyIndex + 1);
        
        // Add new state
        $this->drawingHistory[] = $canvasDataUrl;
        $this->historyIndex = count($this->drawingHistory) - 1;
        
        // Limit history to 20 states
        if (count($this->drawingHistory) > 20) {
            array_shift($this->drawingHistory);
            $this->historyIndex--;
        }
    }

    public function completeDrawing(string $finalCanvasDataUrl): void
    {
        // Convert canvas data to image and save
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $finalCanvasDataUrl));
        $filename = 'drawings/submissions/' . $this->submission->id . '_' . time() . '.png';
        
        Storage::disk('public')->put($filename, $imageData);
        
        // Create thumbnail
        $thumbnailFilename = 'drawings/thumbnails/' . $this->submission->id . '_' . time() . '.png';
        // For now, use the same image as thumbnail - in production you'd resize it
        Storage::disk('public')->put($thumbnailFilename, $imageData);
        
        // Calculate stars based on effort and time
        $this->starsEarned = $this->calculateStars();
        
        // Update submission
        $this->submission->update([
            'artwork_path' => $filename,
            'thumbnail_path' => $thumbnailFilename,
            'completed' => true,
            'completed_at' => now(),
            'stars_earned' => $this->starsEarned,
            'time_spent_seconds' => $this->timeSpent,
            'tools_used' => $this->getToolsUsed(),
            'drawing_data' => [
                'canvas' => $finalCanvasDataUrl,
                'tools_used' => $this->getToolsUsed(),
                'colors_used' => $this->getColorsUsed(),
            ],
        ]);
        
        $this->completed = true;
    }

    protected function calculateStars(): int
    {
        $baseStars = 1; // Everyone gets at least 1 star for trying
        
        // Time bonus (up to 2 stars for spending time)
        $timeBonus = min(2, intdiv($this->timeSpent, 120)); // 1 star per 2 minutes, max 2
        
        // Tool variety bonus (up to 2 stars for using different tools)
        $toolsUsed = count($this->getToolsUsed());
        $toolBonus = min(2, $toolsUsed - 1);
        
        return min(5, $baseStars + $timeBonus + $toolBonus);
    }

    protected function getToolsUsed(): array
    {
        // This would be tracked by the frontend and sent back
        // For now, return the current tool
        return [$this->currentTool];
    }

    protected function getColorsUsed(): array
    {
        // This would be tracked by the frontend and sent back
        // For now, return the current color
        return [$this->currentColor];
    }

    public function resetDrawing(): void
    {
        $this->completed = false;
        $this->starsEarned = 0;
        $this->clearCanvas();
        
        // Reset submission
        $this->submission->update([
            'completed' => false,
            'completed_at' => null,
            'stars_earned' => 0,
            'drawing_data' => null,
        ]);
    }

    public function render()
    {
        return view('livewire.student.drawing-player')->layout('layouts.preview');
    }
}