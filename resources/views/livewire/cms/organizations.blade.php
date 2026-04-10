<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">My Organization</h1>
            <div class="cms-breadcrumb">Management · Tenant Profile · Access</div>
        </div>
        <button type="button" class="btn btn-primary btn-sm" wire:click="openEditModal">Edit profile</button>
    </div>
    @if (session()->has('message'))
        <div style="margin-bottom:12px; padding:10px 14px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:10px; font-size:12px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif
    <div class="cms-stats-row" style="grid-template-columns:repeat(auto-fit, minmax(140px, 1fr));">
        <div class="cms-stat"><div class="cms-stat-val">{{ $totalUsers }}</div><div class="cms-stat-label">Total people</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $adminCount }}</div><div class="cms-stat-label">Admins</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $teacherCount }}</div><div class="cms-stat-label">Teachers</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $studentCount }}</div><div class="cms-stat-label">Students</div></div>
    </div>

    <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6); box-shadow:0 8px 32px rgba(26,18,8,.05)">
        <div class="cms-table-header" style="grid-template-columns:1fr auto; margin:-24px -24px 24px;">
            <span>Organization profile</span>
            <span style="text-align:right; font-weight:600; color:var(--stone)">Read-only overview</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:var(--sp-5)">
            <div>
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Name</div>
                <div style="font-size:16px; font-weight:700; color:var(--ink); line-height:1.4">{{ $name ?: '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Code</div>
                <div style="font-size:15px; font-weight:600; color:var(--ink-light); font-family:ui-monospace,monospace">{{ $code ?: '—' }}</div>
            </div>
            <div style="grid-column:1 / -1;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Address</div>
                <div style="font-size:15px; font-weight:600; color:var(--ink); line-height:1.5">{{ $address !== '' ? $address : '—' }}</div>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Plan</div>
                <span class="status-pill status-published">{{ strtoupper($plan) }}</span>
            </div>
            <div>
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Status</div>
                <span class="status-pill {{ $status === 'active' ? 'status-published' : 'status-draft' }}">{{ strtoupper($status) }}</span>
            </div>
            <div style="grid-column:1 / -1;">
                <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:8px">Description</div>
                <div style="font-size:15px; font-weight:600; color:var(--ink); line-height:1.6; white-space:pre-wrap">{{ $description !== '' ? $description : '—' }}</div>
            </div>
        </div>
    </div>

    @if ($showEditModal)
        <div
            wire:click="closeEditModal"
            style="position:fixed; inset:0; background:rgba(26,18,8,.45); backdrop-filter:blur(6px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px;"
            role="presentation"
        >
            <div
                onclick="event.stopPropagation()"
                style="background:#fff; width:100%; max-width:560px; border-radius:var(--r-xl); border:1px solid var(--cream-mid); box-shadow:0 24px 64px rgba(26,18,8,.18); max-height:90vh; display:flex; flex-direction:column; overflow:hidden;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="org-edit-title"
            >
                <div style="padding:var(--sp-6); border-bottom:1px solid var(--cream-mid); display:flex; align-items:flex-start; justify-content:space-between; gap:var(--sp-4); flex-shrink:0">
                    <div>
                        <h2 id="org-edit-title" style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--ink); margin-bottom:4px">Edit organization</h2>
                        <div style="font-size:12px; color:var(--stone); font-weight:600">Update name, address, and description. Code and plan are managed by the platform.</div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeEditModal"
                        style="flex-shrink:0; width:40px; height:40px; border-radius:12px; border:1px solid var(--cream-mid); background:#fff; color:var(--ink); font-size:20px; line-height:1; cursor:pointer; font-weight:700"
                    >×</button>
                </div>
                <form wire:submit.prevent="save" style="padding:var(--sp-6); overflow-y:auto; display:flex; flex-direction:column; gap:var(--sp-4)">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Name</label>
                        <input type="text" wire:model="editName" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px">
                        @error('editName') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Code</label>
                        <input type="text" value="{{ $code }}" readonly tabindex="-1" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); background:#F8F8F8; color:var(--ink-light); font-family:ui-monospace,monospace; font-size:14px; cursor:not-allowed">
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Address</label>
                        <input type="text" wire:model="editAddress" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px">
                        @error('editAddress') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Description</label>
                        <textarea wire:model="editDescription" rows="4" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px; resize:vertical"></textarea>
                        @error('editDescription') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:var(--sp-2); padding-top:var(--sp-4); border-top:1px solid var(--cream-mid)">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="closeEditModal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">Save changes</span>
                            <span wire:loading wire:target="save">Saving…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
