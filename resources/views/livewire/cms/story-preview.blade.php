<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">{{ $comic->title }}</h1>
            <div class="cms-breadcrumb">Story Details · {{ $comic->tribe?->name ?? 'No Tribe' }} · {{ $comic->age_range }}</div>
        </div>
        <a class="btn btn-ghost btn-sm" href="{{ route('cms.admin.approved-content') }}" style="text-decoration:none;">← Back to Approved Content</a>
    </div>

    <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6); margin-bottom:var(--sp-6);">
        <div style="font-size:14px; color:var(--ink-light); margin-bottom:10px;">{{ $comic->description ?: 'No description provided.' }}</div>
        <div style="display:flex; gap:8px;">
            <span class="status-pill status-published">Published</span>
            <span class="status-pill status-draft">{{ $comic->panels->count() }} Panels</span>
        </div>
    </div>

    <div style="display:grid; gap:var(--sp-6);">
        @foreach($comic->panels as $panel)
            <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6);">
                <div style="font-size:12px; font-weight:800; color:var(--stone); text-transform:uppercase; margin-bottom:10px;">Panel {{ $panel->order_index + 1 }}</div>
                @if($panel->image_path)
                    <img src="{{ asset('storage/'.$panel->image_path) }}" alt="Panel image" style="max-width:100%; border-radius:14px; border:1px solid var(--cream-mid); margin-bottom:12px;">
                @endif
                @if($panel->caption)
                    <div style="margin-bottom:10px; color:var(--ink-light);">{{ $panel->caption }}</div>
                @endif
                @if($panel->audio_url)
                    <audio controls style="width:100%;">
                        <source src="{{ asset('storage/'.$panel->audio_url) }}">
                    </audio>
                @endif
            </div>
        @endforeach
    </div>
</div>
