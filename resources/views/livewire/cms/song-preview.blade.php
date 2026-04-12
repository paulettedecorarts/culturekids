<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">{{ $song->title }}</h1>
            <div class="cms-breadcrumb">Song Details · {{ $song->tribe?->name ?? 'No Tribe' }} · {{ $song->age_range }}</div>
        </div>
        <a class="btn btn-ghost btn-sm" href="{{ route('cms.admin.approved-content') }}" style="text-decoration:none;">← Back to Approved Content</a>
    </div>

    <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6);">
        <div style="margin-bottom:8px; color:var(--ink-light);">{{ $song->description ?: 'No description provided.' }}</div>
        <div style="display:flex; gap:8px; margin-bottom:14px;">
            <span class="status-pill status-published">Published</span>
            <span class="status-pill status-draft">{{ $song->language ?: 'Language n/a' }}</span>
            <span class="status-pill status-draft">{{ $song->song_type }}</span>
        </div>

        @if($song->cover_image_path)
            <img src="{{ asset('storage/'.$song->cover_image_path) }}" alt="Song cover" style="max-width:340px; border-radius:14px; border:1px solid var(--cream-mid); margin-bottom:14px;">
        @endif

        @if($song->audio_path)
            <div style="margin-bottom:14px;">
                <div style="font-size:12px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:6px;">Playback</div>
                @if(str_ends_with(strtolower($song->audio_path), '.mp4') || str_ends_with(strtolower($song->audio_path), '.webm') || str_ends_with(strtolower($song->audio_path), '.mov') || str_ends_with(strtolower($song->audio_path), '.avi'))
                    <video controls style="width:100%; max-width:640px; border-radius:12px;">
                        <source src="{{ asset('storage/'.$song->audio_path) }}">
                    </video>
                @else
                    <audio controls style="width:100%;">
                        <source src="{{ asset('storage/'.$song->audio_path) }}">
                    </audio>
                @endif
            </div>
        @endif

        <div style="font-size:12px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:6px;">Lyrics</div>
        <div style="white-space:pre-wrap; line-height:1.6; color:var(--ink-light);">{{ $song->lyrics ?: 'No lyrics provided.' }}</div>
    </div>
</div>
