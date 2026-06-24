<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\DrawingSubmission;
use App\Models\ProgressEvent;
use App\Models\ReadingProgress;
use App\Models\Song;
use App\Models\User;
use App\Support\AppleGradeScoring;
use App\Support\ChildProfileAccess;
use App\Support\ContentProgressType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChildContentProgressService
{
    public function __construct(
        private readonly OrganisationModuleResolver $moduleResolver,
    ) {}

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>
     */
    public function upsertSession(
        User $user,
        ChildProfile $child,
        string $contentType,
        int $contentId,
        int $currentPosition,
        int $totalPositions,
        ?array $metadata = null,
    ): array {
        ContentProgressType::assertValid($contentType);
        $this->authorizeChild($user, $child);
        $this->assertModuleForType($user, $contentType);
        $this->assertContentExists($contentType, $contentId);

        $progress = ChildContentProgress::query()->firstOrNew([
            'child_profile_id' => $child->id,
            'content_type' => $contentType,
            'content_id' => $contentId,
        ]);

        if (! $progress->exists) {
            $progress->started_at = now();
        }

        if ($progress->status !== 'completed') {
            $progress->current_position = max(0, $currentPosition);
            $progress->total_positions = max(0, $totalPositions);
            $progress->metadata = $metadata ?? $progress->metadata;
            $progress->last_activity_at = now();
            $progress->refreshStatusFromPosition();
            $progress->save();
        }

        // Legacy reading_progress dual-write removed from session ticks — unified table is source of truth.

        return $this->format($progress);
    }

    /**
     * @return array<string, mixed>
     */
    public function complete(
        User $user,
        ChildProfile $child,
        string $contentType,
        int $contentId,
        string $idempotencyKey,
        ?array $performance = null,
    ): array {
        ContentProgressType::assertValid($contentType);
        $this->authorizeChild($user, $child);
        $this->assertModuleForType($user, $contentType);
        $this->assertContentExists($contentType, $contentId);

        $existingByKey = ChildContentProgress::query()
            ->where('completion_idempotency_key', $idempotencyKey)
            ->first();

        if ($existingByKey) {
            return $this->format($existingByKey, alreadyRecorded: true, newStarsEarned: 0);
        }

        $maxStars = $this->resolveStars($contentType, $contentId);
        $gradeInput = (is_array($performance) && is_array($performance['apple_input'] ?? null))
            ? $performance['apple_input']
            : ($performance ?? []);
        $graded = AppleGradeScoring::compute($gradeInput, $maxStars);
        $stars = (int) $graded['stars_earned'];

        $progress = ChildContentProgress::query()->firstOrNew([
            'child_profile_id' => $child->id,
            'content_type' => $contentType,
            'content_id' => $contentId,
        ]);

        $wasCompleted = $progress->status === 'completed';

        if ($wasCompleted) {
            if (! $progress->exists) {
                $progress->started_at = now();
            }

            if ($progress->total_positions > 0) {
                $progress->current_position = $progress->total_positions;
            }

            $progress->metadata = $this->mergeAttemptMetadata(
                is_array($progress->metadata) ? $progress->metadata : [],
                $graded['metadata'],
            );
            $progress->last_activity_at = now();
            if (! $progress->completion_idempotency_key) {
                $progress->completion_idempotency_key = $idempotencyKey;
            }
            $progress->save();

            return $this->format($progress->fresh(), alreadyRecorded: true, newStarsEarned: 0);
        }

        if (! $progress->exists) {
            $progress->started_at = now();
        }

        if ($progress->total_positions > 0) {
            $progress->current_position = $progress->total_positions;
        }

        $progress->status = 'completed';
        $progress->stars_earned = $stars;
        $progress->metadata = array_merge(
            is_array($progress->metadata) ? $progress->metadata : [],
            $graded['metadata'],
        );
        $progress->completed_at = now();
        $progress->last_activity_at = now();
        $progress->completion_idempotency_key = $idempotencyKey;
        $progress->save();

        $child->increment('total_stars', $stars);

        $this->syncLegacyCompletion($user, $child, $contentType, $contentId, $stars, $idempotencyKey);
        $this->persistTypeAttempt($user, $contentType, $contentId, $graded['metadata'], $stars);

        return $this->format($progress->fresh(), alreadyRecorded: false, newStarsEarned: $stars);
    }

    /**
     * @return array<string, mixed>|list<array<string, mixed>>
     */
    public function get(
        User $user,
        ChildProfile $child,
        ?string $contentType = null,
        ?int $contentId = null,
        ?string $status = null,
        ?int $limit = null,
    ): array {
        $this->authorizeChild($user, $child);

        $query = ChildContentProgress::query()
            ->where('child_profile_id', $child->id);

        if ($contentType !== null) {
            ContentProgressType::assertValid($contentType);
            $query->where('content_type', $contentType);
        }

        if ($contentId !== null) {
            $query->where('content_id', $contentId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        } elseif ($contentType === null && $contentId === null) {
            // Catalog badges only need rows the child has touched.
            $query->whereIn('status', ['completed', 'in_progress']);
        }

        if ($contentType !== null && $contentId !== null) {
            $progress = $query->first();

            if (! $progress) {
                return $this->emptyProgress($contentType, $contentId);
            }

            return $this->format($progress);
        }

        return $query
            ->orderByDesc('last_activity_at')
            ->limit(min($limit ?? 500, 1000))
            ->get()
            ->map(fn (ChildContentProgress $row) => $this->format($row))
            ->values()
            ->all();
    }

    public function authorizeChild(User $user, ChildProfile $child): void
    {
        if (! ChildProfileAccess::canAccess($user, $child)) {
            throw new AuthorizationException('Child profile does not belong to this account.');
        }
    }

    private function assertModuleForType(User $user, string $contentType): void
    {
        $moduleKey = ContentProgressType::moduleKey($contentType);

        if ($moduleKey === null) {
            return;
        }

        $this->moduleResolver->assertEnabledForUser($user, $moduleKey);
    }

    private function assertContentExists(string $contentType, int $contentId): void
    {
        match ($contentType) {
            ContentProgressType::STORY => Comic::query()->whereKey($contentId)->exists()
                || throw (new ModelNotFoundException)->setModel(Comic::class, [$contentId]),
            ContentProgressType::SONG => Song::query()->whereKey($contentId)->exists()
                || throw (new ModelNotFoundException)->setModel(Song::class, [$contentId]),
            default => Activity::query()->whereKey($contentId)->exists()
                || throw (new ModelNotFoundException)->setModel(Activity::class, [$contentId]),
        };
    }

    private function resolveStars(string $contentType, int $contentId): int
    {
        return match ($contentType) {
            ContentProgressType::STORY => (int) (Comic::query()->find($contentId)?->star_points ?? 10),
            ContentProgressType::SONG => (int) (Song::query()->find($contentId)?->star_points ?? 10),
            default => (int) (Activity::query()->find($contentId)?->star_points ?? 10),
        };
    }

    private function syncLegacySession(
        User $user,
        ChildProfile $child,
        string $contentType,
        int $contentId,
        ChildContentProgress $progress,
    ): void {
        if ($contentType === ContentProgressType::STORY) {
            $reading = ReadingProgress::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'comic_id' => $contentId,
                ],
                [
                    'current_page' => $progress->current_position,
                    'total_pages' => max($progress->total_positions, 1),
                    'last_read_at' => now(),
                ],
            );
            $reading->updateStatus();
            $reading->save();
        }
    }

    private function syncLegacyCompletion(
        User $user,
        ChildProfile $child,
        string $contentType,
        int $contentId,
        int $stars,
        string $idempotencyKey,
    ): void {
        if ($contentType === ContentProgressType::STORY) {
            $comic = Comic::query()->findOrFail($contentId);
            $totalPages = max($comic->panels()->count(), 1);

            ReadingProgress::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'comic_id' => $contentId,
                ],
                [
                    'current_page' => $totalPages,
                    'total_pages' => $totalPages,
                    'status' => 'completed',
                    'last_read_at' => now(),
                ],
            );

            return;
        }

        if ($contentType === ContentProgressType::SONG) {
            return;
        }

        if (! ProgressEvent::query()->where('idempotency_key', $idempotencyKey)->exists()) {
            ProgressEvent::query()->create([
                'child_profile_id' => $child->id,
                'activity_id' => $contentId,
                'stars_earned' => $stars,
                'idempotency_key' => $idempotencyKey,
                'completed_at' => now(),
                'synced_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function persistTypeAttempt(
        User $user,
        string $contentType,
        int $contentId,
        array $metadata,
        int $starsEarned,
    ): void {
        if (! in_array($contentType, [ContentProgressType::DRAWING_KIT, ContentProgressType::COLOURING], true)) {
            return;
        }

        $activity = Activity::query()->find($contentId);
        $drawingId = $activity?->metadata['legacy_drawing_id'] ?? null;
        if (! $drawingId) {
            return;
        }

        $input = is_array($metadata['apple_input'] ?? null) ? $metadata['apple_input'] : [];
        $durationMs = $input['durationMs'] ?? $input['duration_ms'] ?? null;
        $toolsUsed = $input['tools_used'] ?? $input['toolsUsed'] ?? [];

        DrawingSubmission::query()->updateOrCreate(
            [
                'drawing_id' => (int) $drawingId,
                'user_id' => $user->id,
            ],
            [
                'completed' => true,
                'stars_earned' => $starsEarned,
                'time_spent_seconds' => is_numeric($durationMs) ? max(0, (int) round(((float) $durationMs) / 1000)) : null,
                'tools_used' => is_array($toolsUsed) ? $toolsUsed : [],
                'drawing_data' => $input,
                'completed_at' => now(),
            ],
        );
    }

    /**
     * @param  array<string, mixed>|null  $existing
     * @param  array<string, mixed>  $attempt
     * @return array<string, mixed>
     */
    private function mergeAttemptMetadata(?array $existing, array $attempt): array
    {
        $merged = array_merge($existing ?? [], $attempt);

        $existingBest = $existing['apple_best_grade'] ?? ($existing['apple_grade'] ?? null);
        $attemptGrade = $attempt['apple_grade'] ?? null;

        if ($this->gradeRank(is_string($attemptGrade) ? $attemptGrade : null)
            > $this->gradeRank(is_string($existingBest) ? $existingBest : null)) {
            $merged['apple_best_grade'] = $attemptGrade;
        } elseif (is_string($existingBest)) {
            $merged['apple_best_grade'] = $existingBest;
        }

        if (is_string($attemptGrade)) {
            $merged['apple_last_grade'] = $attemptGrade;
        }

        return $merged;
    }

    private function gradeRank(?string $grade): int
    {
        return match ($grade) {
            'gold' => 3,
            'silver' => 2,
            'bronze' => 1,
            default => 0,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function format(
        ChildContentProgress $progress,
        bool $alreadyRecorded = false,
        ?int $newStarsEarned = null,
    ): array {
        $result = [
            'child_profile_id' => $progress->child_profile_id,
            'content_type' => $progress->content_type,
            'content_id' => $progress->content_id,
            'status' => $progress->status,
            'current_position' => $progress->current_position,
            'total_positions' => $progress->total_positions,
            'percentage' => $progress->percentage,
            'stars_earned' => (int) $progress->stars_earned,
            'metadata' => $progress->metadata,
            'started_at' => $progress->started_at,
            'completed_at' => $progress->completed_at,
            'last_activity_at' => $progress->last_activity_at,
            'already_recorded' => $alreadyRecorded,
        ];

        if ($newStarsEarned !== null || $alreadyRecorded) {
            $result['stars_earned_this_attempt'] = $newStarsEarned ?? 0;
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyProgress(string $contentType, int $contentId): array
    {
        return [
            'child_profile_id' => null,
            'content_type' => $contentType,
            'content_id' => $contentId,
            'status' => 'not_started',
            'current_position' => 0,
            'total_positions' => 0,
            'percentage' => 0,
            'stars_earned' => 0,
            'metadata' => null,
            'started_at' => null,
            'completed_at' => null,
            'last_activity_at' => null,
            'already_recorded' => false,
            'stars_earned_this_attempt' => 0,
        ];
    }
}
