<div class="drawing-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.drawings') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Drawings</a>
            <div class="sa-page-title">{{ $drawing->title }}</div>
            <div class="sa-breadcrumb">{{ $drawing->drawing_type_display }} • {{ $drawing->tribe->name ?? 'Unknown Tribe' }}</div>
        </div>
        <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
            <a href="{{ route($routePrefix . '.drawings.play', $drawing->id) }}" target="_blank" class="btn btn-success" style="text-decoration:none">
                🎨 Try Drawing
            </a>
            <button wire:click="edit" class="btn btn-primary">
                Edit Drawing
            </button>
            <a href="{{ route($routePrefix . '.drawings') }}" class="btn btn-ghost" style="text-decoration:none">
                Back to Drawings
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
                <div class="act-label" style="margin-bottom:var(--sp-3)">Drawing Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Tribe</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $drawing->tribe->name ?? 'N/A' }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Type</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $drawing->drawing_type_display }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Age Range</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $drawing->age_range }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Difficulty</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ ucfirst($drawing->difficulty_level) }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Star Points</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $drawing->star_points }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $drawing->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 
                               ($drawing->status === 'draft' ? 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' : 'background:rgba(255,255,255,.1);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.2)') }}">
                            {{ ucfirst($drawing->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Materials & Tools</div>
                <div style="margin-bottom:var(--sp-3)">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Required Materials</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        @forelse($drawing->materials ?? [] as $material)
                            <span style="background:rgba(255,255,255,.1);padding:2px 8px;border-radius:12px;font-size:10px;color:rgba(255,255,255,.8)">{{ $material }}</span>
                        @empty
                            <span style="color:rgba(255,255,255,.5);font-size:11px">No materials specified</span>
                        @endforelse
                    </div>
                </div>
                
                <div>
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Available Colors</div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(20px,1fr));gap:4px;max-width:200px">
                        @forelse($drawing->color_palette ?? [] as $color)
                            <div style="width:20px;height:20px;background:{{ $color }};border-radius:4px;border:1px solid rgba(255,255,255,.2)"></div>
                        @empty
                            <span style="color:rgba(255,255,255,.5);font-size:11px">Default colors</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        @if($drawing->description)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Description</div>
            <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.5">{{ $drawing->description }}</p>
        </div>
        @endif

        <!-- Template & Preview Images -->
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Template & Preview</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:var(--sp-3)">
                <div style="text-align:center">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Template Image</div>
                    @if($drawing->template_path)
                        <img src="{{ asset('storage/' . $drawing->template_path) }}" alt="Template" style="width:100%;max-width:300px;height:auto;border-radius:8px;border:1px solid rgba(255,255,255,.1)">
                    @else
                        <div style="width:100%;max-width:300px;height:200px;background:rgba(255,255,255,.05);border-radius:8px;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto">
                            <span style="color:rgba(255,255,255,.5);font-size:11px">No template image</span>
                        </div>
                    @endif
                </div>

                <div style="text-align:center">
                    <div style="color:rgba(255,255,255,.8);font-size:12px;font-weight:600;margin-bottom:8px">Preview Image</div>
                    @if($drawing->preview_path)
                        <img src="{{ asset('storage/' . $drawing->preview_path) }}" alt="Preview" style="width:100%;max-width:300px;height:auto;border-radius:8px;border:1px solid rgba(255,255,255,.1)">
                    @else
                        <div style="width:100%;max-width:300px;height:200px;background:rgba(255,255,255,.05);border-radius:8px;border:1px solid rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;margin:0 auto">
                            <span style="color:rgba(255,255,255,.5);font-size:11px">No preview image</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Submission Statistics -->
        @if($drawing->submissions->count() > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Activity Statistics</div>
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:8px;padding:var(--sp-3)">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:var(--sp-3);text-align:center">
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#60A5FA">{{ $drawing->submissions->count() }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Total Attempts</div>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#4A7C59">{{ $drawing->submissions->where('completed', true)->count() }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Completed</div>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#F2CB5A">{{ $drawing->submissions->avg('stars_earned') ? round($drawing->submissions->avg('stars_earned'), 1) : 0 }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Avg Stars</div>
                    </div>
                    <div>
                        <div style="font-size:24px;font-weight:800;color:#9C88FF">{{ $drawing->submissions->avg('time_spent_seconds') ? gmdate('i:s', $drawing->submissions->avg('time_spent_seconds')) : '0:00' }}</div>
                        <div style="color:rgba(255,255,255,.6);font-size:11px">Avg Time</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>