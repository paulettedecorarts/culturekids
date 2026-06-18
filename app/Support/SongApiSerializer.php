<?php

namespace App\Support;

use App\Models\Song;

final class SongApiSerializer
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(Song $song, bool $includeLyrics = false): array
    {
        $song->loadMissing(['tribe:id,name,hero_emoji,hero_icon,color', 'lyricSegments']);

        $payload = [
            'id' => $song->id,
            'title' => $song->title,
            'description' => $song->description,
            'language' => $song->language,
            'song_type' => $song->song_type,
            'activity_type' => $song->activity_type,
            'age_range' => $song->age_range,
            'duration' => $song->duration_label,
            'duration_seconds' => $song->duration_seconds,
            'status' => $song->status,
            'difficulty_level' => $song->difficulty_level,
            'has_karaoke_timing' => (bool) $song->has_karaoke_timing,
            'has_fill_blanks' => (bool) $song->has_fill_blanks,
            'interaction_config' => $song->interaction_config,
            'cover_image' => self::publicUrl($song->cover_image_path),
            'audio_path' => self::publicUrl($song->audio_path),
            'video_path' => self::publicUrl($song->video_path),
            'star_points' => $song->star_points ?? 10,
            'tribe' => $song->tribe ? [
                'id' => $song->tribe->id,
                'name' => $song->tribe->name,
                'icon' => $song->tribe->hero_emoji ?? $song->tribe->hero_icon,
                'color' => $song->tribe->color,
            ] : null,
            'lyric_segments' => $song->lyricSegments
                ->sortBy('order_index')
                ->values()
                ->map(fn ($segment) => [
                    'id' => $segment->id,
                    'order' => $segment->order_index,
                    'text' => $segment->segment_text,
                    'display_text' => $segment->display_text,
                    'start_time' => (float) $segment->start_time,
                    'end_time' => (float) $segment->end_time,
                    'is_fill_blank' => (bool) $segment->is_fill_blank,
                    'blank_answer' => $segment->is_fill_blank ? $segment->blank_answer : null,
                ])
                ->all(),
        ];

        if ($includeLyrics) {
            $payload['lyrics'] = $song->lyrics;
        }

        return $payload;
    }

    private static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
