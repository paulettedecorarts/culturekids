<?php

namespace App\Services;

use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Support\ContentProgressType;
use Illuminate\Support\Collection;

class ChildAchievementService
{
    /**
     * Stars-only progress snapshot for a child profile.
     *
     * Badges, milestones and tribe achievements were scrapped — we only count stars
     * earned plus simple completion totals. `badges`/`milestones` are returned as empty
     * arrays for backward compatibility with older shipped app builds.
     *
     * @return array<string, mixed>
     */
    public function build(ChildProfile $child): array
    {
        $allRows = ChildContentProgress::query()
            ->where('child_profile_id', $child->id)
            ->get(['id', 'content_type', 'content_id', 'status', 'stars_earned', 'metadata', 'current_position', 'total_positions', 'last_activity_at']);

        $completed = $allRows->where('status', 'completed')->values();

        $totalStories = $completed->where('content_type', ContentProgressType::STORY)->count();
        $totalSongs = $completed->where('content_type', ContentProgressType::SONG)->count();
        $totalActivities = $completed
            ->filter(fn (ChildContentProgress $row) => ! in_array(
                $row->content_type,
                [ContentProgressType::STORY, ContentProgressType::SONG],
                true,
            ))
            ->count();

        $gradeCounts = ['gold' => 0, 'silver' => 0, 'bronze' => 0];
        foreach ($completed as $row) {
            $metadata = is_array($row->metadata) ? $row->metadata : [];
            $grade = $metadata['apple_best_grade'] ?? ($metadata['apple_grade'] ?? null);
            if (is_string($grade) && isset($gradeCounts[$grade])) {
                $gradeCounts[$grade]++;
            }
        }

        $progressSnapshot = $this->buildProgressSnapshotFromRows($allRows);

        $completedActivityIds = $completed
            ->filter(fn (ChildContentProgress $row) => ContentProgressType::usesActivityTable($row->content_type))
            ->pluck('content_id')
            ->unique()
            ->values()
            ->all();

        return [
            'child' => [
                'id' => $child->id,
                'name' => $child->name,
                'avatar' => $child->avatar,
                'age_band' => $child->age_band,
                'total_stars' => (int) $child->total_stars,
            ],
            'completed_activity_ids' => $completedActivityIds,
            'total_stars' => (int) $child->total_stars,
            'total_activities_completed' => $totalActivities,
            'total_stories_completed' => $totalStories,
            'total_songs_completed' => $totalSongs,
            'grade_counts' => $gradeCounts,
            // Badges scrapped — kept empty for backward compatibility.
            'badges' => [],
            'milestones' => [],
            'completion_by_type' => $progressSnapshot['completion_by_type'],
            'in_progress_items' => $progressSnapshot['in_progress_items'],
        ];
    }

    /**
     * @param  Collection<int, ChildContentProgress>  $rows
     * @return array{
     *   completion_by_type: array<string, array{completed: int, in_progress: int}>,
     *   in_progress_items: list<array<string, mixed>>
     * }
     */
    private function buildProgressSnapshotFromRows(Collection $rows): array
    {
        $byType = [];
        foreach (ContentProgressType::ALL as $type) {
            $byType[$type] = ['completed' => 0, 'in_progress' => 0];
        }

        $inProgressItems = [];

        foreach ($rows->sortByDesc('last_activity_at') as $row) {
            $type = $row->content_type;
            if (! isset($byType[$type])) {
                continue;
            }

            if ($row->status === 'completed') {
                $byType[$type]['completed']++;
                continue;
            }

            if ($row->status === 'in_progress') {
                $byType[$type]['in_progress']++;
                if (count($inProgressItems) < 20) {
                    $inProgressItems[] = [
                        'content_type' => $type,
                        'content_id' => (int) $row->content_id,
                        'current_position' => (int) $row->current_position,
                        'total_positions' => (int) $row->total_positions,
                        'percentage' => (int) $row->percentage,
                        'last_activity_at' => $row->last_activity_at,
                    ];
                }
            }
        }

        return [
            'completion_by_type' => $byType,
            'in_progress_items' => $inProgressItems,
        ];
    }

    /**
     * @deprecated Use buildProgressSnapshotFromRows — kept for tests.
     * @return array{
     *   completion_by_type: array<string, array{completed: int, in_progress: int}>,
     *   in_progress_items: list<array<string, mixed>>
     * }
     */
    private function buildProgressSnapshot(int $childProfileId): array
    {
        $rows = ChildContentProgress::query()
            ->where('child_profile_id', $childProfileId)
            ->orderByDesc('last_activity_at')
            ->get([
                'content_type',
                'content_id',
                'status',
                'current_position',
                'total_positions',
                'percentage',
                'last_activity_at',
            ]);

        return $this->buildProgressSnapshotFromRows($rows);
    }
}
