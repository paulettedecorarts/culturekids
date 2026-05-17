<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Stories & Comics</div>
            <div class="sa-breadcrumb">Cultural learning content · Panel-based stories</div>
        </div>
        <a href="{{ route($storyRouteBase . '.create') }}" class="btn btn-primary btn-sm" style="text-decoration:none;display:inline-flex;align-items:center">📖 Create story</a>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:var(--sp-3);margin-bottom:var(--sp-5)">
        <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:16px;padding:20px">
            <div style="font-size:28px;font-weight:800;color:var(--cms-text);margin-bottom:4px">{{ $stats['total'] }}</div>
            <div style="font-size:11px;color:var(--cms-text-muted);font-weight:700;text-transform:uppercase">Total Stories</div>
        </div>
        <div style="background:rgba(74,124,89,0.1);border:1px solid rgba(74,124,89,0.2);border-radius:16px;padding:20px">
            <div style="font-size:28px;font-weight:800;color:var(--banana-light);margin-bottom:4px">{{ $stats['published'] }}</div>
            <div style="font-size:11px;color:var(--cms-text-muted);font-weight:700;text-transform:uppercase">Published</div>
        </div>
        <div style="background:rgba(212,160,23,0.1);border:1px solid rgba(212,160,23,0.2);border-radius:16px;padding:20px">
            <div style="font-size:28px;font-weight:800;color:var(--savanna-gold);margin-bottom:4px">{{ $stats['draft'] }}</div>
            <div style="font-size:11px;color:var(--cms-text-muted);font-weight:700;text-transform:uppercase">Draft</div>
        </div>
        <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:16px;padding:20px">
            <div style="font-size:28px;font-weight:800;color:var(--cms-text);margin-bottom:4px">{{ $tribes->count() }}</div>
            <div style="font-size:11px;color:var(--cms-text-muted);font-weight:700;text-transform:uppercase">Tribes</div>
        </div>
    </div>

    <!-- Filters -->
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:var(--sp-3);margin-bottom:var(--sp-5)">
        <input wire:model.live="searchTerm" type="text" placeholder="🔍 Search stories..." style="background: var(--cms-surface-raised);border: 1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none">

        <select wire:model.live="filterTribe" style="background: var(--cms-surface-raised);border: 1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer">
            <option value="" style="background:var(--indigo-night)">All Tribes</option>
            @foreach($tribes as $tribe)
                <option value="{{ $tribe->id }}" style="background:var(--indigo-night)">{{ $tribe->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" style="background: var(--cms-surface-raised);border: 1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer">
            <option value="" style="background:var(--indigo-night)">All Status</option>
            <option value="published" style="background:var(--indigo-night)">Published</option>
            <option value="review" style="background:var(--indigo-night)">In Review</option>
            <option value="draft" style="background:var(--indigo-night)">Draft</option>
        </select>

        <select wire:model.live="filterAgeRange" style="background: var(--cms-surface-raised);border: 1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer">
            <option value="" style="background:var(--indigo-night)">All Ages</option>
            <option value="2-3" style="background:var(--indigo-night)">2-3 years</option>
            <option value="3-4" style="background:var(--indigo-night)">3-4 years</option>
            <option value="4-5" style="background:var(--indigo-night)">4-5 years</option>
            <option value="5-6" style="background:var(--indigo-night)">5-6 years</option>
        </select>
    </div>

    <!-- Stories Grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:var(--sp-4)">
        @forelse($stories as $story)
            <div style="background:var(--cms-surface-raised);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);overflow:hidden;transition:all 0.3s" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 32px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
                <div style="height:180px;background:linear-gradient(135deg, rgba(196,75,43,0.3), rgba(107,32,16,0.3));display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden">
                    @if($story->cover_image_path)
                        @if(str_ends_with(strtolower($story->cover_image_path), '.pdf'))
                            <span style="font-size:64px">📄</span>
                        @else
                            <img src="{{ asset('storage/' . $story->cover_image_path) }}" alt="" style="width:100%;height:100%;object-fit:cover">
                        @endif
                    @else
                        <span style="font-size:64px">📖</span>
                    @endif

                    @if($story->status === 'published')
                        <span style="position:absolute;top:12px;right:12px;background:rgba(74,124,89,0.9);color:var(--cms-text);padding:4px 12px;border-radius:20px;font-size:10px;font-weight:800">PUBLISHED</span>
                    @elseif($story->status === 'review')
                        <span style="position:absolute;top:12px;right:12px;background:rgba(232,135,42,0.9);color:var(--cms-text);padding:4px 12px;border-radius:20px;font-size:10px;font-weight:800">IN REVIEW</span>
                    @else
                        <span style="position:absolute;top:12px;right:12px;background:rgba(212,160,23,0.9);color:var(--cms-text);padding:4px 12px;border-radius:20px;font-size:10px;font-weight:800">DRAFT</span>
                    @endif
                </div>

                <div style="padding:var(--sp-4)">
                    <div style="display:flex;align-items:start;justify-content:space-between;margin-bottom:var(--sp-2)">
                        <div style="flex:1">
                            <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:var(--cms-text);margin-bottom:4px">
                                {{ $story->title }}
                            </div>
                            <div style="font-size:11px;color:var(--cms-text-muted);font-weight:700">
                                {{ $story->tribe->name }} · {{ $story->age_range }} years
                            </div>
                        </div>
                    </div>

                    @if($story->description)
                        <p style="font-size:12px;color:var(--cms-text-muted);margin-bottom:var(--sp-3);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                            {{ $story->description }}
                        </p>
                    @endif

                    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-3);padding-top:var(--sp-3);border-top:1px solid rgba(255,255,255,.05)">
                        <div style="font-size:11px;color:var(--cms-text-muted)">
                            ⭐ {{ $story->star_points }} points
                        </div>
                        @if($story->panels_count > 0)
                            <div style="font-size:11px;color:var(--cms-text-muted)">
                                📄 {{ $story->panels_count }} panels
                            </div>
                        @endif
                    </div>

                    <div style="display:flex;gap:var(--sp-2);flex-wrap:wrap">
                        <a
                            href="{{ route($storyRouteBase . '.detail', $story->id) }}"
                            class="btn btn-sm"
                            style="flex:1;min-width:72px;background:rgba(212,160,23,.15);color:var(--savanna-gold);border:1px solid rgba(212,160,23,.3);font-size:10px;padding:8px;text-decoration:none;display:flex;align-items:center;justify-content:center"
                        >
                            View
                        </a>
                        @if(($canPublishContent ?? false) || $story->status !== 'published')
                            <button
                                wire:click="togglePublish({{ $story->id }})"
                                class="btn btn-sm"
                                style="flex:1;min-width:72px;background:rgba(74,124,89,.15);color:var(--banana-light);border:1px solid rgba(74,124,89,.3);font-size:10px;padding:8px"
                            >
                                @if($canPublishContent ?? false)
                                    {{ $story->status === 'published' ? 'Unpublish' : 'Publish' }}
                                @else
                                    {{ $story->status === 'review' ? 'Withdraw to draft' : 'Submit for review' }}
                                @endif
                            </button>
                        @else
                            <span class="btn btn-sm" style="flex:1;min-width:72px;opacity:.5;font-size:10px;padding:8px;text-align:center;border:1px dashed var(--cms-border);border-radius:8px">Published</span>
                        @endif
                        <a
                            href="{{ route($storyRouteBase . '.edit', $story->id) }}"
                            class="btn btn-sm"
                            style="flex:1;min-width:72px;background:var(--cms-surface-raised);color:var(--cms-text);border:1px solid var(--cms-border);font-size:10px;padding:8px;text-decoration:none;display:flex;align-items:center;justify-content:center"
                        >
                            Edit
                        </a>
                        <button
                            wire:click="delete({{ $story->id }})"
                            wire:confirm="Delete this story and all its assets?"
                            class="btn btn-sm"
                            style="background:rgba(196,75,43,.15);color:var(--clay-red-light);border:1px solid rgba(196,75,43,.3);font-size:10px;padding:8px 12px"
                        >
                            🗑
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1;text-align:center;color:var(--cms-text-muted);padding:var(--sp-12)">
                <div style="font-size:64px;margin-bottom:var(--sp-4)">📖</div>
                <div style="font-size:16px;font-weight:700;margin-bottom:var(--sp-2)">No stories created</div>
                <div style="font-size:13px;margin-bottom:var(--sp-4)">Create your first story to start building your cultural content library.</div>
                <a href="{{ route($storyRouteBase . '.create') }}" class="btn btn-primary btn-sm" style="text-decoration:none">📖 Create story</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:var(--sp-6)">
        {{ $stories->links(data: ['scrollTo' => false]) }}
    </div>
</div>
