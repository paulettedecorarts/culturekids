<div class="song-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Activities</a>
            <div class="sa-page-title">{{ $song->title }}</div>
            <div class="sa-breadcrumb">{{ ucfirst($song->activity_type) }} Activity • {{ ucfirst($song->song_type) }} Song</div>
        </div>
        <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
            <a href="{{ route($routePrefix . '.songs.activities.preview', ['id' => $song->id]) }}" target="_blank" class="btn btn-success" style="text-decoration:none">
                🎵 Preview Activity
            </a>
            <button wire:click="edit" class="btn btn-primary">
                Edit Song
            </button>
            <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-ghost" style="text-decoration:none">
                Back to Activities
            </a>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-content-card" style="margin-bottom:var(--sp-4)">
        <!-- Basic Info -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-4)">
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Song Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Tribe</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $song->tribe->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Language</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ ucfirst($song->language ?? 'Not specified') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Age Range</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $song->age_range }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Difficulty</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ ucfirst($song->difficulty_level ?? 'Not specified') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Star Points</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $song->star_points }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $song->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 
                               ($song->status === 'draft' ? 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' : 'background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.2)') }}">
                            {{ ucfirst($song->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Interactive Features</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:16px;height:16px;display:flex;align-items:center;justify-content:center">
                            @if($song->has_karaoke_timing)
                                <span style="color:#4A7C59;font-size:14px">✓</span>
                            @else
                                <span style="color:rgba(255,255,255,.4);font-size:14px">✗</span>
                            @endif
                        </span>
                        <span style="color:rgba(255,255,255,.8);font-size:12px">Karaoke Timing</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="width:16px;height:16px;display:flex;align-items:center;justify-content:center">
                            @if($song->has_fill_blanks)
                                <span style="color:#4A7C59;font-size:14px">✓</span>
                            @else
                                <span style="color:rgba(255,255,255,.4);font-size:14px">✗</span>
                            @endif
                        </span>
                        <span style="color:rgba(255,255,255,.8);font-size:12px">Fill-the-Blanks</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Media Files -->
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Media Files</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:var(--sp-3)">
                <div style="text-align:center">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Audio</div>
                    @if($song->audio_path)
                        <audio controls style="width:100%;max-width:300px">
                            <source src="{{ asset('storage/' . $song->audio_path) }}" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>
                    @else
                        <p style="color:rgba(255,255,255,.5);font-size:11px;margin:0">No audio file</p>
                    @endif
                </div>

                <div style="text-align:center">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Video</div>
                    @if($song->video_path)
                        <video controls style="width:100%;max-width:300px;max-height:200px">
                            <source src="{{ asset('storage/' . $song->video_path) }}" type="video/mp4">
                            Your browser does not support the video element.
                        </video>
                    @else
                        <p style="color:rgba(255,255,255,.5);font-size:11px;margin:0">No video file</p>
                    @endif
                </div>

                <div style="text-align:center">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Cover Image</div>
                    @if($song->cover_image_path)
                        <img src="{{ asset('storage/' . $song->cover_image_path) }}" alt="Cover" style="width:100%;max-width:200px;height:120px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,.1)">
                    @else
                        <div style="width:100%;max-width:200px;height:120px;background:rgba(255,255,255,.05);border-radius:8px;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto">
                            <span style="color:rgba(255,255,255,.5);font-size:11px">No cover image</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($song->description)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Description</div>
            <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.5">{{ $song->description }}</p>
        </div>
        @endif

        <!-- Lyrics -->
        @if($song->lyrics)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Lyrics</div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:var(--sp-3)">
                <pre style="white-space:pre-wrap;color:rgba(255,255,255,.85);font-size:12px;font-family:var(--font-mono);line-height:1.6;margin:0">{{ $song->lyrics }}</pre>
            </div>
        </div>
        @endif

        <!-- Lyric Segments -->
        @if($song->has_karaoke_timing && $song->lyricSegments->count() > 0)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Karaoke Segments ({{ $song->lyricSegments->count() }})</div>
            <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                @foreach($song->lyricSegments as $segment)
                <div style="border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:var(--sp-3);background:rgba(255,255,255,.02)">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ ucfirst($segment->segment_type) }} #{{ $segment->order_index + 1 }}</span>
                        <span style="color:rgba(255,255,255,.6);font-size:10px">{{ $segment->start_time }}s - {{ $segment->end_time }}s</span>
                    </div>
                    <p style="color:rgba(255,255,255,.85);font-size:12px;margin:0">{{ $segment->segment_text }}</p>
                    @if($segment->is_fill_blank && $segment->blank_answer)
                    <div style="margin-top:8px">
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;background:rgba(59,130,246,.2);color:#60A5FA;border:1px solid rgba(59,130,246,.35)">
                            Fill-the-blank: {{ $segment->blank_answer }}
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Activity Stats -->
        @if($song->activities->count() > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Activity Statistics</div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:var(--sp-3)">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:var(--sp-3);text-align:center">
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#60A5FA">{{ $song->activities->count() }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Total Plays</div>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#4A7C59">{{ $song->activities->where('completed', true)->count() }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Completed</div>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#F2CB5A">{{ $song->activities->avg('stars_earned') ? round($song->activities->avg('stars_earned'), 1) : 0 }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Avg Stars</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>