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
    <div class="sa-filter-bar stories-filter-bar" style="margin-bottom:var(--sp-5)">
        <input wire:model.live.debounce.300ms="searchTerm" type="text" placeholder="🔍 Search stories..." class="stories-filter-search" style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;width:100%">

        <select wire:model.live="filterTribe" class="stories-filter-select" style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer;width:100%">
            <option value="" style="background:var(--cms-input-bg)">All Tribes</option>
            @foreach($tribes as $tribe)
                <option value="{{ $tribe->id }}" style="background:var(--cms-input-bg)">{{ $tribe->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="stories-filter-select" style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer;width:100%">
            <option value="" style="background:var(--cms-input-bg)">All Status</option>
            <option value="published" style="background:var(--cms-input-bg)">Published</option>
            <option value="review" style="background:var(--cms-input-bg)">In Review</option>
            <option value="draft" style="background:var(--cms-input-bg)">Draft</option>
        </select>

        <select wire:model.live="filterAgeRange" class="stories-filter-select" style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:12px 16px;color:var(--cms-text);font-size:13px;outline:none;cursor:pointer;width:100%">
            <option value="" style="background:var(--cms-input-bg)">All Ages</option>
            <option value="2-3" style="background:var(--cms-input-bg)">2-3 years</option>
            <option value="3-4" style="background:var(--cms-input-bg)">3-4 years</option>
            <option value="4-5" style="background:var(--cms-input-bg)">4-5 years</option>
            <option value="5-6" style="background:var(--cms-input-bg)">5-6 years</option>
        </select>
    </div>

    <!-- Stories Grid -->
    <div class="cms-card-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:var(--sp-4)">
        @forelse($stories as $story)
            <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:var(--r-xl);overflow:hidden;transition:all 0.3s" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 32px rgba(0,0,0,0.3)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
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

                    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-3);padding-top:var(--sp-3);border-top:1px solid var(--cms-border-subtle)">
                        <div style="font-size:11px;color:var(--cms-text-muted)">
                            ⭐ {{ $story->star_points }} points
                        </div>
                        @if($story->panels_count > 0)
                            <div style="font-size:11px;color:var(--cms-text-muted)">
                                📄 {{ $story->panels_count }} panels
                            </div>
                        @endif
                    </div>

                    <div class="sa-table-actions" style="width:100%">
                        <a
                            href="{{ route($storyRouteBase . '.detail', $story->id) }}"
                            class="sa-table-action sa-table-action--accent sa-table-action--grow"
                        >
                            View
                        </a>
                        @if(($canPublishContent ?? false) || $story->status !== 'published')
                            <button
                                type="button"
                                wire:click="togglePublish({{ $story->id }})"
                                class="sa-table-action sa-table-action--grow"
                                style="background:rgba(74,124,89,.15);color:var(--banana-light);border-color:rgba(74,124,89,.35)"
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
                            class="sa-table-action sa-table-action--grow"
                        >
                            Edit
                        </a>
                        <button
                            type="button"
                            wire:click="delete({{ $story->id }})"
                            wire:confirm="Delete this story and all its assets?"
                            class="sa-table-action sa-table-action--danger"
                        >
                            Delete
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

    <style>
        .stories-filter-bar { align-items: stretch; }
        .stories-filter-bar .stories-filter-search { flex: 2 1 280px; min-width: 200px; }
        .stories-filter-bar .stories-filter-select { flex: 1 1 140px; min-width: 140px; max-width: 220px; }
    </style>
</div>
