<?php

namespace App\Livewire\Teacher;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Comic;
use App\Models\LessonPlan;
use App\Support\TeacherActiveClassroom;
use App\Support\TeacherCatalogScope;
use App\Support\TeacherPrintScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Dashboard extends Component
{
    /** @var array<int, Classroom> */
    public array $classrooms = [];

    public ?int $activeClassroomId = null;

    public int $weekOffset = 0;

    public bool $showCreateModal = false;

    public string $content_kind = 'comic';

    public ?int $selected_comic_id = null;

    public ?int $selected_activity_id = null;

    public string $form_scheduled_on = '';

    public string $form_notes = '';

    public function mount(): void
    {
        $this->form_scheduled_on = Carbon::today()->toDateString();
        $this->refreshClassrooms();
    }

    public function refreshClassrooms(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->classrooms = [];
            $this->activeClassroomId = null;

            return;
        }

        $this->classrooms = TeacherActiveClassroom::teachingClassroomsFor($user)->values()->all();
        $active = TeacherActiveClassroom::activeClassroom($user);
        $this->activeClassroomId = $active?->id;
    }

    public function updatedActiveClassroomId(?int $value): void
    {
        $user = auth()->user();
        if ($user && $value) {
            TeacherActiveClassroom::setActiveClassroomId($user, $value);
        }
    }

    public function updatedContentKind(string $value): void
    {
        // Clear the opposite selection when switching content type
        if ($value === 'comic') {
            $this->selected_activity_id = null;
        } else {
            $this->selected_comic_id = null;
        }
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->content_kind = 'comic';
        $this->selected_comic_id = null;
        $this->selected_activity_id = null;
        $this->form_scheduled_on = Carbon::today()->toDateString();
        $this->form_notes = '';
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function saveLesson(): void
    {
        $user = auth()->user();
        if (! $user || ! $this->activeClassroomId) {
            Log::warning('Lesson save attempted without user or active classroom.', [
                'user_id' => $user?->id,
                'activeClassroomId' => $this->activeClassroomId
            ]);
            session()->flash('error', __('Select a class first.'));
            $this->closeCreateModal();

            return;
        }

        $classroom = Classroom::query()->findOrFail($this->activeClassroomId);
        $this->authorizeClassroom($classroom);

        Log::info('Saving lesson plan...', [
            'user_id' => $user->id,
            'classroom_id' => $classroom->id,
            'content_kind' => $this->content_kind,
            'selected_comic_id' => $this->selected_comic_id,
            'selected_activity_id' => $this->selected_activity_id,
            'scheduled_on' => $this->form_scheduled_on
        ]);

        try {
            Log::info('Starting validation...');
            $rules = [
                'content_kind' => 'required|in:comic,activity',
                'form_scheduled_on' => 'required|date',
                'form_notes' => 'nullable|string|max:2000',
            ];

            if ($this->content_kind === 'comic') {
                $rules['selected_comic_id'] = 'required|exists:comics,id';
            } else {
                $rules['selected_activity_id'] = 'required|exists:activities,id';
            }

            $validated = $this->validate($rules, [], [
                'form_scheduled_on' => __('date'),
            ]);
            Log::info('Validation passed.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation failed.', ['errors' => $e->errors()]);
            throw $e;
        }

        if ($this->content_kind === 'comic') {
            Log::info('Resolving comic model...');
            $comic = Comic::query()->findOrFail((int) $this->selected_comic_id);
            Log::info('Checking comic scope...');
            if (! TeacherCatalogScope::userCanViewComic($user, $comic)) {
                Log::warning('User unauthorized to assign comic to lesson.', ['user_id' => $user->id, 'comic_id' => $comic->id]);
                session()->flash('error', __('You cannot assign this story to a lesson.'));
                $this->closeCreateModal();

                return;
            }
            $lessonableId = $comic->id;
            $lessonableType = Comic::class;
        } else {
            Log::info('Resolving activity model...');
            try {
                $activity = Activity::query()->findOrFail((int) $this->selected_activity_id);
                Log::info('Activity model found.');
            } catch (\Exception $e) {
                Log::error('Activity model NOT found.', ['id' => $this->selected_activity_id, 'error' => $e->getMessage()]);
                throw $e;
            }

            Log::info('Checking activity scope...');
            $scopeQuery = TeacherPrintScope::activitiesQueryFor($user)->where('activities.id', $activity->id);
            Log::info('Scope query generated.', ['sql' => $scopeQuery->toSql(), 'bindings' => $scopeQuery->getBindings()]);
            
            if (! $scopeQuery->exists()) {
                Log::warning('Activity not found in user scope during lesson save.', [
                    'user_id' => $user->id,
                    'activity_id' => $activity->id
                ]);
                session()->flash('error', __('You cannot assign this activity.'));
                $this->closeCreateModal();

                return;
            }
            Log::info('Activity scope check passed.');
            $lessonableId = $activity->id;
            $lessonableType = Activity::class;
        }

        $maxOrder = (int) LessonPlan::query()
            ->where('classroom_id', $classroom->id)
            ->whereDate('scheduled_on', $this->form_scheduled_on)
            ->max('sort_order');

        Log::info('Creating lesson plan record...', [
            'classroom_id' => $classroom->id,
            'lessonable_id' => $lessonableId,
            'lessonable_type' => $lessonableType,
            'sort_order' => $maxOrder + 1
        ]);

        try {
            $lesson = LessonPlan::query()->create([
                'classroom_id' => $classroom->id,
                'lessonable_id' => $lessonableId,
                'lessonable_type' => $lessonableType,
                'scheduled_on' => $this->form_scheduled_on,
                'status' => LessonPlan::STATUS_PLANNED,
                'sort_order' => $maxOrder + 1,
                'notes' => trim($this->form_notes) !== '' ? $this->form_notes : null,
                'created_by' => $user->id,
            ]);
            Log::info('Lesson plan saved successfully.', ['lesson_plan_id' => $lesson->id]);
        } catch (\Exception $e) {
            Log::error('FAILED to create lesson plan.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }

        $this->closeCreateModal();
        session()->flash('message', __('Lesson added.'));
        $this->dispatch('$refresh');
    }

    public function markCompleted(int $lessonPlanId): void
    {
        $plan = LessonPlan::query()->with('classroom')->findOrFail($lessonPlanId);
        $this->authorizeClassroom($plan->classroom);

        $plan->update(['status' => LessonPlan::STATUS_COMPLETED]);
    }

    public function markPlanned(int $lessonPlanId): void
    {
        $plan = LessonPlan::query()->with('classroom')->findOrFail($lessonPlanId);
        $this->authorizeClassroom($plan->classroom);

        $plan->update(['status' => LessonPlan::STATUS_PLANNED]);
    }

    public function deleteLesson(int $lessonPlanId): void
    {
        $plan = LessonPlan::query()->with('classroom')->findOrFail($lessonPlanId);
        $this->authorizeClassroom($plan->classroom);

        $plan->delete();
        session()->flash('message', __('Lesson removed.'));
    }

    private function authorizeClassroom(Classroom $classroom): void
    {
        $user = auth()->user();
        abort_unless(
            $user
            && (int) $classroom->organisation_id === (int) $user->organisation_id
            && (int) $classroom->teacher_id === (int) $user->id,
            403
        );
    }

    public function slotLabel(LessonPlan $plan): string
    {
        if ($plan->isCompleted()) {
            return 'done';
        }
        $d = $plan->scheduled_on;
        if ($d->isToday()) {
            return 'today';
        }
        if ($d->isTomorrow()) {
            return 'tomorrow';
        }
        if ($d->isPast()) {
            return 'overdue';
        }

        return 'upcoming';
    }

    public function render()
    {
        $user = auth()->user();
        $this->refreshClassrooms();

        $activeClassroom = $this->activeClassroomId
            ? collect($this->classrooms)->firstWhere('id', $this->activeClassroomId)
            : null;

        $weekStart = Carbon::now()->addWeeks($this->weekOffset)->startOfWeek(Carbon::MONDAY);
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY);

        $lessonPlans = collect();
        $stats = [
            ['label' => __('Lessons planned'), 'val' => '0', 'delta' => ''],
            ['label' => __('Students'), 'val' => '0', 'delta' => ''],
            ['label' => __('Avg completion'), 'val' => '—', 'delta' => ''],
        ];

        $comicOptions = collect();
        $activityOptions = collect();

        if ($user && $activeClassroom) {
            $lessonPlans = LessonPlan::query()
                ->where('classroom_id', $activeClassroom->id)
                ->whereBetween('scheduled_on', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->orderBy('scheduled_on')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $lessonPlans->loadMorph('lessonable', [
                Comic::class => ['tribe'],
                Activity::class => ['tribe'],
            ]);

            $stats[0]['val'] = (string) $lessonPlans->count();
            $stats[1]['val'] = (string) $activeClassroom->children()->count();

            $comicOptions = TeacherCatalogScope::comicsQueryFor($user)->limit(300)->get(['id', 'title', 'tribe_id']);
            $activityOptions = TeacherPrintScope::activitiesQueryFor($user)->limit(300)->get(['id', 'title', 'type', 'tribe_id']);
        }

        return view('livewire.teacher.dashboard', [
            'lessonPlans' => $lessonPlans,
            'stats' => $stats,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekLabel' => $weekStart->isoFormat('MMM D').' – '.$weekEnd->isoFormat('MMM D, YYYY'),
            'activeClassroom' => $activeClassroom,
            'comicOptions' => $comicOptions,
            'activityOptions' => $activityOptions,
        ]);
    }
}
