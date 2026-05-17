<div class="age-categories-page">
    <div class="age-header">
        <div>
            <div class="sa-page-title">Age Categories</div>
            <div class="sa-breadcrumb">Super Admin · Content · Age policy, UI scaling, and module access control</div>
        </div>
        <a class="btn btn-primary btn-sm age-create-btn" href="{{ route('admin.age-categories.create') }}">+ New Category</a>
    </div>

    @if (session()->has('message'))
        <div class="age-flash">{{ session('message') }}</div>
    @endif

    <section class="age-list">
        @foreach($categories as $category)
            <article class="age-card">
                <div class="age-card-top" style="background: linear-gradient(135deg, {{ $category->color ?? '#313441' }}, #1f222d);">
                    <div class="age-card-title">
                        <span>{{ $category->icon_emoji ?: '📚' }}</span>
                        <div>
                            <h3>{{ $category->name }}</h3>
                            <p>{{ $category->age_range_label }} years · {{ $category->key }}</p>
                        </div>
                    </div>
                    <span class="age-status {{ $category->is_active ? 'on' : 'off' }}">
                        {{ $category->is_active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </div>

                <div class="age-card-body">
                    <div class="age-kv">
                        <span>Scale</span>
                        <strong>{{ ucfirst($category->ui_scale) }}</strong>
                    </div>
                    <div class="age-kv">
                        <span>Touch target</span>
                        <strong>{{ $category->touch_target_px }}px</strong>
                    </div>
                    <div class="age-kv">
                        <span>Reading</span>
                        <strong>{{ str_replace('_', ' ', $category->reading_level) }}</strong>
                    </div>
                    <div class="age-kv">
                        <span>Complexity</span>
                        <strong>{{ str_replace('_', ' ', $category->activity_complexity) }}</strong>
                    </div>
                    <div class="age-kv">
                        <span>Assigned children</span>
                        <strong>{{ $category->child_profiles_count }}</strong>
                    </div>
                    <div class="age-kv">
                        <span>Audio first</span>
                        <strong>{{ $category->is_audio_first ? 'Yes' : 'No' }}</strong>
                    </div>
                </div>

                <div class="age-tags">
                    @foreach(($category->ui_features ?? []) as $feature)
                        <span>{{ $feature }}</span>
                    @endforeach
                </div>

                <div class="age-actions">
                    <a class="btn btn-sm" href="{{ route('admin.age-categories.detail', ['id' => $category->id]) }}">Details</a>
                </div>
            </article>
        @endforeach
    </section>
<style>
    .age-categories-page .age-header{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
    .age-categories-page .age-create-btn{background:var(--savanna-gold);color:#101010;border:none;border-radius:999px;font-weight:700;padding:8px 14px}
    .age-categories-page .age-flash,.age-categories-page .age-error{padding:10px 12px;border-radius:10px;margin-bottom:12px;font-size:13px}
    .age-categories-page .age-flash{background:rgba(74,124,89,.22);border:1px solid rgba(74,124,89,.45);color:#8ed3a8}
    .age-categories-page .age-error{background:rgba(196,75,43,.2);border:1px solid rgba(196,75,43,.45);color:#ffb8a6}
    .age-categories-page .age-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:14px;align-content:start}
    .age-categories-page .age-card{border:1px solid var(--cms-border);border-radius:14px;background:var(--cms-surface);overflow:hidden;display:flex;flex-direction:column}
    .age-categories-page .age-card-top{padding:14px;display:flex;justify-content:space-between;gap:12px;align-items:flex-start;min-height:86px}
    .age-categories-page .age-card-title{display:flex;gap:10px;align-items:flex-start}
    .age-categories-page .age-card-title span{font-size:24px;line-height:1}
    .age-categories-page .age-card-title h3{margin:0;color:var(--cms-text);font-size:16px;font-weight:700}
    .age-categories-page .age-card-title p{margin:2px 0 0;color: var(--cms-text);font-size:12px}
    .age-categories-page .age-status{font-size:10px;font-weight:800;padding:4px 9px;border-radius:999px}
    .age-categories-page .age-status.on{background:rgba(74,124,89,.3);color:#9fe8b9}
    .age-categories-page .age-status.off{background: var(--cms-surface-raised);color:var(--cms-text-muted)}
    .age-categories-page .age-card-body{padding:12px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .age-categories-page .age-kv{background:var(--cms-surface);padding:9px;border-radius:8px}
    .age-categories-page .age-kv span{display:block;font-size:10px;color: var(--cms-text-muted);margin-bottom:2px}
    .age-categories-page .age-kv strong{font-size:12px;color:var(--cms-text);text-transform:capitalize}
    .age-categories-page .age-tags{padding:0 12px 10px;display:flex;flex-wrap:wrap;gap:6px}
    .age-categories-page .age-tags span{font-size:10px;background:var(--cms-input-bg);padding:4px 7px;border-radius:999px;color: var(--cms-text)}
    .age-categories-page .age-actions{padding:0 12px 12px;display:flex;gap:8px;margin-top:auto}
    .age-categories-page .age-actions .btn{padding:7px 11px;border-radius:8px;border:1px solid rgba(255,255,255,.14);background:var(--cms-surface-raised);color:var(--cms-text);font-size:12px;font-weight:700}
    .age-categories-page .age-actions .btn-danger{background:rgba(196,75,43,.2);border-color:rgba(196,75,43,.42);color:#ffd3c7}
    @media (max-width: 640px){.age-categories-page .age-list{grid-template-columns:1fr}}
</style>
</div>
