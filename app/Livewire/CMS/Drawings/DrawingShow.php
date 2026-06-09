<?php

namespace App\Livewire\CMS\Drawings;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Drawing;
use Livewire\Component;

class DrawingShow extends Component
{
    use UsesPortalContext;

    public Drawing $drawing;

    public function mount(int $id): void
    {
        $this->drawing = Drawing::with(['tribe', 'submissions.user'])->findOrFail($id);
    }

    public function contentRoutePrefix(): string
    {
        if (request()->routeIs('admin.colouring.*', 'cms.editor.colouring.*', 'cms.admin.approved-content.colouring.*', 'teacher.library.colouring.*')) {
            return 'colouring';
        }

        return \App\Support\ActivityDrawingTypeFilter::isColouringType($this->drawing->drawing_type)
            ? 'colouring'
            : 'drawings';
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName($this->contentRoutePrefix().'.edit'),
            ['id' => $this->drawing->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.drawings.drawing-show', [
            'routePrefix' => $this->portalRoutePrefix(),
            'contentRoutePrefix' => $this->contentRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}