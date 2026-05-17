<div class="org-create-root">
    <style>
        .org-create-root {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            --oc-stone: #9c8875;
        }
        .org-create-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-4);
            flex-wrap: wrap;
            margin-bottom: var(--sp-6);
        }
        .org-create-shell {
            width: 100%;
            max-width: 100%;
            background: var(--cms-surface-raised);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: var(--r-xl);
            padding: clamp(var(--sp-5), 2vw, var(--sp-8));
            box-sizing: border-box;
        }
        .org-create-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(var(--sp-5), 2vw, var(--sp-8));
            align-items: start;
        }
        @media (min-width: 960px) {
            .org-create-grid {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                gap: var(--sp-8);
            }
        }
        .org-create-panel {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: var(--sp-5);
        }
        .org-create-panel-title {
            font-size: 11px;
            font-weight: 800;
            color: var(--savanna-gold);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 0 0 4px;
            padding-bottom: var(--sp-3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .org-field label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: var(--oc-stone);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .org-field input,
        .org-field select,
        .org-field textarea {
            width: 100%;
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            border-radius: 12px;
            padding: 14px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }
        .org-field-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-5);
        }
        @media (min-width: 640px) {
            .org-field-row { grid-template-columns: 1fr 1fr; }
        }
        .org-create-actions {
            margin-top: var(--sp-6);
            padding-top: var(--sp-6);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-4);
            align-items: center;
        }
        .org-admin-lead {
            font-size: 13px;
            color: var(--cms-text-muted);
            line-height: 1.55;
            margin: 0;
        }
    </style>

    <div class="org-create-head">
        <div>
            <div class="sa-page-title">New organization</div>
            <div class="sa-breadcrumb">
                <a href="{{ route('admin.organizations') }}" wire:navigate style="color:rgba(212,160,23,.8);text-decoration:none;font-weight:600">Organizations</a>
                <span style="color: var(--cms-text-muted)"> / </span>
                <span>Create</span>
            </div>
        </div>
        <a href="{{ route('admin.organizations') }}" wire:navigate class="btn btn-sm" style="border:1px solid var(--cms-input-border);color: var(--cms-text);text-decoration:none">Cancel</a>
    </div>

    <div class="org-create-shell">
        <form wire:submit.prevent="save">
            <div class="org-create-grid">
                <section class="org-create-panel" aria-labelledby="org-create-org-heading">
                    <h2 id="org-create-org-heading" class="org-create-panel-title">Organization details</h2>

                    <div class="org-field">
                        <label>Branding (logo)</label>
                        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                            <div style="width:72px;height:72px;border-radius:14px;background: var(--cms-surface-raised);border: 1px solid var(--cms-border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover">
                                @else
                                    <span style="font-size:12px;font-weight:700;color: var(--cms-text-muted);text-transform:uppercase">Logo</span>
                                @endif
                            </div>
                            <input type="file" wire:model="logo" accept="image/*" style="font-size:12px;color: var(--cms-text-muted);max-width:100%">
                        </div>
                        @error('logo') <div style="color:var(--clay-red);font-size:11px;margin-top:8px;font-weight:600">{{ $message }}</div> @enderror
                    </div>

                    <div class="org-field">
                        <label for="org-name">School name</label>
                        <input id="org-name" wire:model.live="name" type="text" autocomplete="organization">
                        @error('name') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                    </div>

                    <div class="org-field-row">
                        <div class="org-field">
                            <label for="org-code">System code</label>
                            <input id="org-code" wire:model="code" type="text" placeholder="e.g. kisu-ug" autocomplete="off">
                            @error('code') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                        </div>
                        <div class="org-field">
                            <label for="org-status">Access status</label>
                            <select id="org-status" wire:model="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="org-field">
                        <label for="org-desc">Description</label>
                        <textarea id="org-desc" wire:model="description" rows="4" placeholder="Optional"></textarea>
                        @error('description') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                    </div>

                    <div class="org-field-row">
                        <div class="org-field">
                            <label for="org-plan">Plan tier</label>
                            <select id="org-plan" wire:model="plan">
                                <option value="free">Free</option>
                                <option value="school">School</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                            @error('plan') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                        </div>
                        <div class="org-field">
                            <label for="org-address">Physical address</label>
                            <input id="org-address" wire:model="address" type="text" autocomplete="street-address">
                            @error('address') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </section>

                <section class="org-create-panel" aria-labelledby="org-create-admin-heading">
                    <h2 id="org-create-admin-heading" class="org-create-panel-title">Organisation administrator</h2>
                    <p class="org-admin-lead">This person receives an email with a link to choose a password. The <strong style="color: var(--cms-text-muted)">org_admin</strong> role is assigned automatically.</p>

                    <div class="org-field">
                        <label for="admin-name">Admin full name</label>
                        <input id="admin-name" wire:model="admin_name" type="text" autocomplete="name">
                        @error('admin_name') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                    </div>

                    <div class="org-field">
                        <label for="admin-email">Admin email</label>
                        <input id="admin-email" wire:model="admin_email" type="email" autocomplete="email">
                        @error('admin_email') <div style="color:var(--clay-red);font-size:11px;margin-top:6px">{{ $message }}</div> @enderror
                    </div>
                </section>
            </div>

            <div class="org-create-actions">
                <button type="submit" class="btn btn-primary" style="padding:14px 28px;border-radius:14px;font-weight:700">Create organization</button>
                <a href="{{ route('admin.organizations') }}" wire:navigate class="btn btn-sm" style="border:1px solid var(--cms-input-border);color: var(--cms-text);text-decoration:none;padding:12px 20px">Back to list</a>
            </div>
        </form>
    </div>
</div>
