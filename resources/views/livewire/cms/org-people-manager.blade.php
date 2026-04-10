<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Teachers &amp; students</h1>
            <div class="cms-breadcrumb">Invite people · Your organisation</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end">
            @if ($organization)
                <button type="button" class="btn btn-primary btn-sm" wire:click="openInviteModal">Invite member</button>
            @endif
            <a href="{{ route('cms.admin.organizations') }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none">Organization profile</a>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="margin-bottom:16px; padding:12px 16px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:12px; font-size:13px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif

    @if (! $organization)
        <div style="padding:24px; background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); color:var(--stone); font-weight:600">
            Your account is not linked to an organisation. Contact support.
        </div>
    @else
        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:1fr 1.2fr auto;">
                <span>Name</span>
                <span>Email</span>
                <span>Roles</span>
            </div>
            @forelse($members as $member)
                <div class="cms-table-row" style="grid-template-columns:1fr 1.2fr auto; cursor:default">
                    <div>
                        <div class="cms-asset-name">{{ $member->name }}</div>
                        <div class="cms-asset-sub">Joined {{ $member->created_at->diffForHumans() }}</div>
                    </div>
                    <div style="font-size:14px; font-weight:600; color:var(--ink-light); word-break:break-word">{{ $member->email }}</div>
                    <div style="display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end">
                        @forelse($member->roles as $role)
                            <span class="status-pill status-draft" style="text-transform:capitalize; font-size:9px">{{ str_replace('_', ' ', $role->name) }}</span>
                        @empty
                            <span style="font-size:12px; color:var(--stone)">—</span>
                        @endforelse
                    </div>
                </div>
            @empty
                <div style="padding:32px; text-align:center; color:var(--stone); font-weight:600">No people in this organisation yet.</div>
            @endforelse
        </div>
    @endif

    @if ($organization && $showInviteModal)
        <div
            wire:click="closeInviteModal"
            style="position:fixed; inset:0; background:rgba(26,18,8,.45); backdrop-filter:blur(6px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px;"
            role="presentation"
        >
            <div
                onclick="event.stopPropagation()"
                style="background:#fff; width:100%; max-width:560px; border-radius:var(--r-xl); border:1px solid var(--cream-mid); box-shadow:0 24px 64px rgba(26,18,8,.18); max-height:90vh; display:flex; flex-direction:column; overflow:hidden;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="org-invite-title"
            >
                <div style="padding:var(--sp-6); border-bottom:1px solid var(--cream-mid); display:flex; align-items:flex-start; justify-content:space-between; gap:var(--sp-4); flex-shrink:0">
                    <div>
                        <h2 id="org-invite-title" style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--ink); margin-bottom:4px">Send invitation</h2>
                        <div style="font-size:12px; color:var(--stone); font-weight:600; line-height:1.45">
                            Add a <strong>teacher</strong> or <strong>student</strong> to {{ $organization->name }}. They get an email with a link to set their password, then they can sign in.
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeInviteModal"
                        style="flex-shrink:0; width:40px; height:40px; border-radius:12px; border:1px solid var(--cream-mid); background:#fff; color:var(--ink); font-size:20px; line-height:1; cursor:pointer; font-weight:700"
                    >×</button>
                </div>
                <form wire:submit.prevent="invite" style="padding:var(--sp-6); overflow-y:auto; display:flex; flex-direction:column; gap:var(--sp-4)">
                    @if (config('mail.default') === 'log')
                        <div style="padding:10px 14px; background:#FFFBEB; border:1px solid #FDE68A; border-radius:10px; font-size:12px; color:#92400E; font-weight:600">
                            Mail is in <strong>log</strong> mode locally — messages go to <code style="font-size:11px">storage/logs/laravel.log</code>. Use <strong>MAIL_MAILER=smtp</strong> for real inbox delivery.
                        </div>
                    @endif
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Full name</label>
                        <input type="text" wire:model="inviteName" autocomplete="name" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px">
                        @error('inviteName') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Email</label>
                        <input type="email" wire:model="inviteEmail" autocomplete="email" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px">
                        @error('inviteEmail') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Role</label>
                        <select wire:model="inviteRole" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); font-family:var(--font-admin); font-size:15px; background:#fff">
                            <option value="teacher">Teacher</option>
                            <option value="student">Student</option>
                        </select>
                        @error('inviteRole') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:var(--sp-2); padding-top:var(--sp-4); border-top:1px solid var(--cream-mid)">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="closeInviteModal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="invite">Send invitation</span>
                            <span wire:loading wire:target="invite">Sending…</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
