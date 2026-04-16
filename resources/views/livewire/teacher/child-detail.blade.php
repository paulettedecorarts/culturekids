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
        <div style="display:flex; align-items:center; gap:20px">
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
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:20px">
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
                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:16px">
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
                            <div style="display:flex; justify-content:space-between; align-items:start">
                                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:20px; flex:1">
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
</div>
