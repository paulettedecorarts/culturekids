<style>
    @media (max-width: 768px) {
        .child-detail-grid {
            grid-template-columns: 1fr !important;
        }
        .child-detail-header {
            flex-direction: column !important;
            align-items: flex-start !important;
            gap: 16px !important;
        }
        .child-detail-meta {
            flex-direction: column !important;
            gap: 12px !important;
        }
        .filter-tabs {
            flex-wrap: wrap !important;
        }
        .badge-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
        }
    }
    
    @media (max-width: 480px) {
        .badge-grid {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<div class="teacher-class-hub">
    <div class="header">
        <div>
            <h1 class="page-title">{{ $child->name }}</h1>
            <div class="breadcrumb">
                <a href="{{ route('teacher.my-class') }}" style="color:var(--sky-dusk); text-decoration:none">← Back to My Class</a>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:#D1FAE5; border:1px solid #6EE7B7; border-radius:16px; padding:16px; font-size:14px; font-weight:600; color:#065F46; margin-bottom:24px">
            {{ session('message') }}
        </div>
    @endif

    <!-- Child Header -->
    <div style="background:#fff; border-radius:24px; padding:32px; border:1px solid var(--cream-mid); box-shadow:var(--shadow-md); margin-bottom:24px">
        <div class="child-detail-header" style="display:flex; align-items:center; gap:20px">
            <div style="width:64px; height:64px; border-radius:16px; background:var(--sky-dusk); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:800; font-size:28px">
                {{ strtoupper(substr($child->name, 0, 1)) }}
            </div>
            <div style="flex:1">
                <h2 style="font-size:24px; font-weight:800; color:var(--ink); margin-bottom:4px">{{ $child->name }}</h2>
                <p style="font-size:14px; color:var(--stone); margin-bottom:8px">{{ $child->email }}</p>
                <div style="display:flex; gap:8px">
                    @foreach($child->roles as $role)
                        <span style="display:inline-flex; padding:4px 12px; border-radius:12px; font-size:11px; font-weight:700; background:var(--sunfire-pale); color:var(--sunfire)">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Child Profiles -->
    <div style="background:#fff; border-radius:24px; padding:32px; border:1px solid var(--cream-mid); box-shadow:var(--shadow-md)">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px">
            <h2 style="font-size:20px; font-weight:800; color:var(--ink)">Child Profiles</h2>
            @if($canEdit && !$creatingProfile)
                <button 
                    wire:click="startCreateProfile"
                    style="padding:10px 20px; background:var(--sky-dusk); color:#fff; border:none; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s"
                    onmouseover="this.style.background='var(--indigo-night)'"
                    onmouseout="this.style.background='var(--sky-dusk)'"
                >
                    + Add Profile
                </button>
            @endif
        </div>
        
        @if($creatingProfile)
            <!-- Create Profile Form -->
            <div style="background:#F0F7FF; border:2px solid var(--sky-dusk); border-radius:16px; padding:24px; margin-bottom:24px">
                <h3 style="font-size:16px; font-weight:700; color:var(--ink); margin-bottom:20px">Create New Profile</h3>
                <form wire:submit.prevent="createProfile">
                    <div class="child-detail-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Name *</label>
                            <input 
                                type="text" 
                                wire:model="newName"
                                style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                            >
                            @error('newName') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Date of Birth *</label>
                            <input 
                                type="date" 
                                wire:model="newDob"
                                style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                            >
                            @error('newDob') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Age Band *</label>
                            <select 
                                wire:model="newAgeBand"
                                style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                            >
                                <option value="simple">Simple</option>
                                <option value="guided">Guided</option>
                                <option value="advanced">Advanced</option>
                                <option value="full">Full</option>
                            </select>
                            @error('newAgeBand') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div style="display:flex; gap:12px">
                        <button 
                            type="submit"
                            style="padding:10px 24px; background:var(--sky-dusk); color:#fff; border:none; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer"
                        >
                            Create Profile
                        </button>
                        <button 
                            type="button"
                            wire:click="cancelCreate"
                            style="padding:10px 24px; background:var(--cream-mid); color:var(--ink); border:none; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        @endif
        
        @if($childProfiles->isEmpty() && !$creatingProfile)
            <p style="color:var(--stone); font-size:14px; font-weight:600">No child profiles found for this student. Click "Add Profile" to create one.</p>
        @else
            <div style="display:flex; flex-direction:column; gap:16px">
                @foreach($childProfiles as $profile)
                    <div style="border:1px solid var(--cream-mid); border-radius:16px; padding:20px">
                        @if($editingProfile === $profile->id)
                            <!-- Edit Mode -->
                            <form wire:submit.prevent="saveProfile">
                                <div class="child-detail-grid" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px">
                                    <div>
                                        <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Name</label>
                                        <input 
                                            type="text" 
                                            wire:model="editName"
                                            style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                                        >
                                        @error('editName') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Date of Birth</label>
                                        <input 
                                            type="date" 
                                            wire:model="editDob"
                                            style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                                        >
                                        @error('editDob') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label style="display:block; font-size:12px; font-weight:700; color:var(--stone); margin-bottom:6px; text-transform:uppercase; letter-spacing:1px">Age Band</label>
                                        <select 
                                            wire:model="editAgeBand"
                                            style="width:100%; padding:10px 14px; border:1px solid var(--cream-mid); border-radius:8px; font-size:14px; font-weight:600"
                                        >
                                            <option value="simple">Simple</option>
                                            <option value="guided">Guided</option>
                                            <option value="advanced">Advanced</option>
                                            <option value="full">Full</option>
                                        </select>
                                        @error('editAgeBand') <span style="color:#DC2626; font-size:12px; display:block; margin-top:4px">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div style="display:flex; gap:12px">
                                    <button 
                                        type="submit"
                                        style="padding:10px 24px; background:var(--sky-dusk); color:#fff; border:none; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer"
                                    >
                                        Save Changes
                                    </button>
                                    <button 
                                        type="button"
                                        wire:click="cancelEdit"
                                        style="padding:10px 24px; background:var(--cream-mid); color:var(--ink); border:none; border-radius:12px; font-size:13px; font-weight:700; cursor:pointer"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        @else
                            <!-- View Mode -->
                            <div style="display:flex; justify-content:space-between; align-items:start; flex-wrap:wrap; gap:16px">
                                <div class="child-detail-meta" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:20px; flex:1; min-width:0">
                                    <div>
                                        <p style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px">Name</p>
                                        <p style="font-size:16px; font-weight:800; color:var(--ink)">{{ $profile->name }}</p>
                                    </div>
                                    
                                    <div>
                                        <p style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px">Date of Birth</p>
                                        <p style="font-size:14px; font-weight:700; color:var(--ink)">{{ \Carbon\Carbon::parse($profile->dob)->format('M d, Y') }}</p>
                                        <p style="font-size:12px; color:var(--stone)">Age: {{ \Carbon\Carbon::parse($profile->dob)->age }} years</p>
                                    </div>
                                    
                                    <div>
                                        <p style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px">Age Band</p>
                                        <span style="display:inline-flex; padding:4px 12px; border-radius:12px; font-size:12px; font-weight:700; 
                                            @if($profile->age_band === 'simple') background:#D1FAE5; color:#065F46;
                                            @elseif($profile->age_band === 'guided') background:#DBEAFE; color:#1E40AF;
                                            @elseif($profile->age_band === 'advanced') background:#E9D5FF; color:#6B21A8;
                                            @else background:#FEE2E2; color:#991B1B;
                                            @endif">
                                            {{ ucfirst($profile->age_band) }}
                                        </span>
                                    </div>
                                    
                                    <div>
                                        <p style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px">Total Stars</p>
                                        <p style="font-size:18px; font-weight:800; color:var(--sunfire)">⭐ {{ number_format($profile->calculated_total_stars ?? 0) }}</p>
                                        <p style="font-size:11px; color:var(--stone)">
                                            Activities: {{ number_format($profile->total_stars) }} | 
                                            Stories: {{ number_format(($profile->calculated_total_stars ?? 0) - $profile->total_stars) }}
                                        </p>
                                    </div>
                                </div>
                                
                                @if($canEdit)
                                    <button 
                                        wire:click="startEditProfile({{ $profile->id }})"
                                        style="padding:8px 16px; background:transparent; color:var(--sky-dusk); border:1px solid var(--sky-dusk); border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; margin-left:16px"
                                    >
                                        Edit
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Activity History -->
    <div style="background:#fff; border-radius:24px; padding:32px; border:1px solid var(--cream-mid); box-shadow:var(--shadow-md); margin-top:24px">
        <h2 style="font-size:20px; font-weight:800; color:var(--ink); margin-bottom:20px">Activity History</h2>
        
        <!-- Filter Tabs -->
        <div class="filter-tabs" style="display:flex; gap:8px; margin-bottom:24px; border-bottom:2px solid var(--cream-mid); padding-bottom:12px">
            <button 
                wire:click="setActivityFilter('all')"
                style="padding:8px 16px; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; {{ $activityFilter === 'all' ? 'background:var(--sky-dusk); color:#fff' : 'background:transparent; color:var(--stone)' }}"
            >
                All
            </button>
            <button 
                wire:click="setActivityFilter('completed')"
                style="padding:8px 16px; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; {{ $activityFilter === 'completed' ? 'background:var(--sky-dusk); color:#fff' : 'background:transparent; color:var(--stone)' }}"
            >
                Completed
            </button>
            <button 
                wire:click="setActivityFilter('in_progress')"
                style="padding:8px 16px; border:none; border-radius:8px; font-size:13px; font-weight:700; cursor:pointer; {{ $activityFilter === 'in_progress' ? 'background:var(--sky-dusk); color:#fff' : 'background:transparent; color:var(--stone)' }}"
            >
                In Progress
            </button>
        </div>
        
        <!-- Stories -->
        @if($stories->isNotEmpty())
            <div style="margin-bottom:32px">
                <h3 style="font-size:16px; font-weight:700; color:var(--ink); margin-bottom:16px">📚 Stories</h3>
                <div style="display:flex; flex-direction:column; gap:12px">
                    @foreach($stories as $progress)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; border:1px solid var(--cream-mid); border-radius:12px; flex-wrap:wrap; gap:12px">
                            <div style="flex:1; min-width:200px">
                                <p style="font-size:14px; font-weight:700; color:var(--ink); margin-bottom:4px">{{ $progress->comic->title }}</p>
                                <p style="font-size:12px; color:var(--stone)">
                                    {{ $progress->comic->tribe->name }} • 
                                    Page {{ $progress->current_page }}/{{ $progress->total_pages }}
                                </p>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px">
                                <span style="padding:4px 12px; border-radius:12px; font-size:11px; font-weight:700; 
                                    {{ $progress->status === 'completed' ? 'background:#D1FAE5; color:#065F46' : 'background:#FEF3C7; color:#92400E' }}">
                                    {{ ucfirst(str_replace('_', ' ', $progress->status)) }}
                                </span>
                                @if($progress->status === 'completed')
                                    <span style="font-size:14px; font-weight:700; color:var(--sunfire)">⭐ {{ $progress->comic->star_points }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        <!-- Activities -->
        @if($activities->isNotEmpty())
            <div>
                <h3 style="font-size:16px; font-weight:700; color:var(--ink); margin-bottom:16px">🎯 Activities</h3>
                <div style="display:flex; flex-direction:column; gap:12px">
                    @foreach($activities as $event)
                        <div style="display:flex; justify-content:space-between; align-items:center; padding:16px; border:1px solid var(--cream-mid); border-radius:12px; flex-wrap:wrap; gap:12px">
                            <div style="flex:1; min-width:200px">
                                <p style="font-size:14px; font-weight:700; color:var(--ink); margin-bottom:4px">{{ $event->activity->title }}</p>
                                <p style="font-size:12px; color:var(--stone)">
                                    {{ $event->activity->tribe->name }} • 
                                    {{ ucfirst($event->activity->type) }} • 
                                    {{ \Carbon\Carbon::parse($event->completed_at)->format('M d, Y') }}
                                </p>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px">
                                <span style="padding:4px 12px; border-radius:12px; font-size:11px; font-weight:700; background:#D1FAE5; color:#065F46">
                                    Completed
                                </span>
                                <span style="font-size:14px; font-weight:700; color:var(--sunfire)">⭐ {{ $event->stars_earned }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if($stories->isEmpty() && $activities->isEmpty())
            <p style="color:var(--stone); font-size:14px; text-align:center; padding:40px">No activities found</p>
        @endif
    </div>

    <!-- Badges & Achievements -->
    <div style="background:#fff; border-radius:24px; padding:32px; border:1px solid var(--cream-mid); box-shadow:var(--shadow-md); margin-top:24px">
        <h2 style="font-size:20px; font-weight:800; color:var(--ink); margin-bottom:20px">🏆 Badges & Achievements</h2>
        
        @if(!empty($badges))
            <div class="badge-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px">
                @foreach($badges as $badge)
                    <div style="padding:20px; border:2px solid {{ $badge['unlocked'] ? 'var(--sunfire)' : 'var(--cream-mid)' }}; border-radius:16px; text-align:center; {{ $badge['unlocked'] ? 'background:var(--sunfire-pale)' : 'background:#F9FAFB; opacity:0.6' }}">
                        <div style="font-size:48px; margin-bottom:12px">{{ $badge['icon'] }}</div>
                        <p style="font-size:14px; font-weight:800; color:var(--ink); margin-bottom:4px">{{ $badge['title'] }}</p>
                        <p style="font-size:11px; color:var(--stone); margin-bottom:12px">{{ $badge['description'] }}</p>
                        <div style="background:#fff; border-radius:8px; padding:8px; margin-top:8px">
                            <div style="display:flex; justify-content:space-between; font-size:11px; font-weight:700; color:var(--stone); margin-bottom:4px">
                                <span>Progress</span>
                                <span>{{ $badge['current'] }}/{{ $badge['target'] }}</span>
                            </div>
                            <div style="background:var(--cream-mid); height:6px; border-radius:3px; overflow:hidden">
                                <div style="background:{{ $badge['unlocked'] ? 'var(--sunfire)' : 'var(--sky-dusk)' }}; height:100%; width:{{ min(100, ($badge['current'] / $badge['target']) * 100) }}%; transition:width .3s"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--stone); font-size:14px; text-align:center; padding:40px">No badges available yet. Create a child profile to start tracking achievements!</p>
        @endif
    </div>
</div>
