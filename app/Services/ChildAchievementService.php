<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ChildContentProgress;
use App\Models\ChildProfile;
use App\Models\Comic;
use App\Support\ContentProgressType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ChildAchievementService
{
    /**
     * Unified achievements for a child profile (all 12 content types + grades).
     *
     * @return array<string, mixed>
     */
    public function build(ChildProfile $child): array
    {
        $completed = ChildContentProgress::query()
            ->where('child_profile_id', $child->id)
            ->where('status', 'completed')
            ->get(['id', 'content_type', 'content_id', 'stars_earned', 'metadata']);

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

        $badges = $this->buildTribeBadges($completed);
        $completedTribes = $badges->filter(
            fn (array $badge) => ($badge['completed_activities'] ?? 0) >= ($badge['total_activities'] ?? 1)
        )->count();

        $milestones = $this->buildMilestones(
            $child,
            $totalActivities,
            $totalStories,
            $totalSongs,
            $completedTribes,
            $gradeCounts,
        );

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
            'tribes_completed' => $completedTribes,
            'badges' => $badges->values()->all(),
            'milestones' => $milestones,
        ];
    }

    /**
     * @param  Collection<int, ChildContentProgress>  $completed
     * @return Collection<int, array<string, mixed>>
     */
    private function buildTribeBadges(Collection $completed): Collection
    {
        $byTribe = [];
        $tribeLookup = $this->buildTribeIdLookup($completed);

        foreach ($completed as $row) {
            $tribeId = $this->resolveTribeIdFromLookup(
                $tribeLookup,
                $row->content_type,
                (int) $row->content_id,
            );
            if ($tribeId === null) {
                continue;
            }

            if (! isset($byTribe[$tribeId])) {
                $byTribe[$tribeId] = [
                    'completed_activities' => 0,
                    'stars_earned' => 0,
                ];
            }

            $byTribe[$tribeId]['completed_activities']++;
            $byTribe[$tribeId]['stars_earned'] += (int) $row->stars_earned;
        }

        if ($byTribe === []) {
            return collect();
        }

        $tribes = DB::table('tribes')
            ->whereIn('id', array_keys($byTribe))
            ->get(['id', 'name', 'hero_emoji', 'color'])
            ->keyBy('id');

        $activityTotals = DB::table('activities')
            ->select('tribe_id')
            ->selectRaw('COUNT(*) as total')
            ->whereIn('tribe_id', array_keys($byTribe))
            ->groupBy('tribe_id')
            ->pluck('total', 'tribe_id');

        $storyTotals = DB::table('comics')
            ->select('tribe_id')
            ->selectRaw('COUNT(*) as total')
            ->where('status', 'published')
            ->whereIn('tribe_id', array_keys($byTribe))
            ->groupBy('tribe_id')
            ->pluck('total', 'tribe_id');

        return collect($byTribe)->map(function (array $stats, int $tribeId) use ($tribes, $activityTotals, $storyTotals) {
            $tribe = $tribes->get($tribeId);
            $total = (int) (($activityTotals[$tribeId] ?? 0) + ($storyTotals[$tribeId] ?? 0));

            return [
                'tribe_id' => $tribeId,
                'tribe_name' => $tribe->name ?? 'Tribe',
                'tribe_icon' => $tribe->hero_emoji ?? '🏛️',
                'tribe_color' => $tribe->color ?? '#E8872A',
                'completed_activities' => $stats['completed_activities'],
                'completed_stories' => $stats['completed_activities'],
                'total_activities' => max($total, 1),
                'total_stories' => max($total, 1),
                'stars_earned' => $stats['stars_earned'],
                'unlocked' => $stats['completed_activities'] > 0,
            ];
        });
    }

    /**
     * Batch-resolve tribe IDs (3 queries instead of one per completion row).
     *
     * @param  Collection<int, ChildContentProgress>  $completed
     * @return array<string, array<int, int|null>>
     */
    private function buildTribeIdLookup(Collection $completed): array
    {
        $storyIds = $completed
            ->where('content_type', ContentProgressType::STORY)
            ->pluck('content_id')
            ->unique()
            ->values()
            ->all();
        $songIds = $completed
            ->where('content_type', ContentProgressType::SONG)
            ->pluck('content_id')
            ->unique()
            ->values()
            ->all();
        $activityIds = $completed
            ->filter(fn (ChildContentProgress $row) => ContentProgressType::usesActivityTable($row->content_type))
            ->pluck('content_id')
            ->unique()
            ->values()
            ->all();

        return [
            ContentProgressType::STORY => $storyIds === []
                ? []
                : Comic::query()->whereIn('id', $storyIds)->pluck('tribe_id', 'id')->all(),
            ContentProgressType::SONG => $songIds === []
                ? []
                : DB::table('songs')->whereIn('id', $songIds)->pluck('tribe_id', 'id')->all(),
            'activity' => $activityIds === []
                ? []
                : Activity::query()->whereIn('id', $activityIds)->pluck('tribe_id', 'id')->all(),
        ];
    }

    /**
     * @param  array<string, array<int, int|null>>  $tribeLookup
     */
    private function resolveTribeIdFromLookup(array $tribeLookup, string $contentType, int $contentId): ?int
    {
        return match ($contentType) {
            ContentProgressType::STORY => $tribeLookup[ContentProgressType::STORY][$contentId] ?? null,
            ContentProgressType::SONG => $tribeLookup[ContentProgressType::SONG][$contentId] ?? null,
            default => $tribeLookup['activity'][$contentId] ?? null,
        };
    }

    /**
     * @param  array{gold: int, silver: int, bronze: int}  $gradeCounts
     * @return list<array<string, mixed>>
     */
    private function buildMilestones(
        ChildProfile $child,
        int $totalActivities,
        int $totalStories,
        int $totalSongs,
        int $completedTribes,
        array $gradeCounts,
    ): array {
        $totalStars = (int) $child->total_stars;
        $goldGrades = $gradeCounts['gold'];

        return [
            [
                'id' => 'first_steps',
                'title' => 'First Steps',
                'description' => 'Complete your first activity',
                'icon' => '👣',
                'target' => 1,
                'current' => $totalActivities,
                'unlocked' => $totalActivities >= 1,
                'type' => 'activities',
            ],
            [
                'id' => 'getting_started',
                'title' => 'Getting Started',
                'description' => 'Complete 10 activities',
                'icon' => '🌱',
                'target' => 10,
                'current' => $totalActivities,
                'unlocked' => $totalActivities >= 10,
                'type' => 'activities',
            ],
            [
                'id' => 'dedicated_learner',
                'title' => 'Dedicated Learner',
                'description' => 'Complete 50 activities',
                'icon' => '🌳',
                'target' => 50,
                'current' => $totalActivities,
                'unlocked' => $totalActivities >= 50,
                'type' => 'activities',
            ],
            [
                'id' => 'story_starter',
                'title' => 'Story Starter',
                'description' => 'Finish your first story',
                'icon' => '📖',
                'target' => 1,
                'current' => $totalStories,
                'unlocked' => $totalStories >= 1,
                'type' => 'stories',
            ],
            [
                'id' => 'songbird',
                'title' => 'Songbird',
                'description' => 'Complete your first song',
                'icon' => '🎵',
                'target' => 1,
                'current' => $totalSongs,
                'unlocked' => $totalSongs >= 1,
                'type' => 'songs',
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
            [
                'id' => 'gold_star_collector',
                'title' => 'Gold Star Collector',
                'description' => 'Earn 10 gold grades',
                'icon' => '⭐',
                'target' => 10,
                'current' => $goldGrades,
                'unlocked' => $goldGrades >= 10,
                'type' => 'grades',
            ],
            [
                'id' => 'tribe_explorer',
                'title' => 'Tribe Explorer',
                'description' => 'Complete 1 tribe',
                'icon' => '🗺️',
                'target' => 1,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 1,
                'type' => 'tribes',
            ],
            [
                'id' => 'heritage_hero',
                'title' => 'Heritage Hero',
                'description' => 'Complete 5 tribes',
                'icon' => '🦸',
                'target' => 5,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 5,
                'type' => 'tribes',
            ],
            [
                'id' => 'activity_champion',
                'title' => 'Activity Champion',
                'description' => 'Complete 100 activities',
                'icon' => '🎯',
                'target' => 100,
                'current' => $totalActivities,
                'unlocked' => $totalActivities >= 100,
                'type' => 'activities',
            ],
        ];
    }
}
