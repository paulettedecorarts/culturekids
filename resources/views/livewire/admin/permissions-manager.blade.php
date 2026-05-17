<div>
    <div style="margin-bottom:var(--sp-5)">
        <div class="sa-page-title">Permissions Overview</div>
        <div class="sa-breadcrumb">Role-based access control — read-only summary</div>
    </div>
    
    <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:var(--r-xl);overflow:hidden">
        <table style="width:100%;border-collapse:collapse;font-size:11px">
            <thead>
                <tr style="background:var(--cms-input-bg)">
                    <th style="padding:var(--sp-3) var(--sp-4);text-align:left;color:var(--cms-text-muted);font-size:9px;letter-spacing:1px;text-transform:uppercase;font-weight:700">Permission</th>
                    @foreach($roles as $role)
                    <th style="padding:var(--sp-3);text-align:center;font-size:9px;font-weight:700;
                        @if($role->name === 'super_admin') color:var(--savanna-gold);
                        @elseif($role->name === 'org_admin') color:#E06444;
                        @elseif($role->name === 'cms_editor') color:#6FA882;
                        @elseif($role->name === 'teacher') color:#4A72C4;
                        @elseif($role->name === 'parent') color:#B07D52;
                        @elseif($role->name === 'child') color:#F2A84E;
                        @else color:#ccc; @endif
                    ">
                        {{ Str::title(str_replace('_', ' ', $role->name)) }}
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($permissions as $permission)
                <tr style="border-bottom:1px solid var(--cms-border-subtle)">
                    <td style="padding:var(--sp-2) var(--sp-4);color:var(--cms-text-muted)">{{ Str::ucfirst(str_replace('_', ' ', $permission->name)) }}</td>
                    @foreach($roles as $role)
                        @if($role->name === 'super_admin')
                            <td style="text-align:center;color:#6FA882">✓</td>
                        @elseif($role->hasPermissionTo($permission->name))
                            <td style="text-align:center;color:#6FA882">✓</td>
                        @else
                            <td style="text-align:center;color: var(--cms-text-muted)">—</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
                
                @if(count($permissions) === 0)
                <tr>
                    <td colspan="{{ count($roles) + 1 }}" style="padding:var(--sp-4);text-align:center;color:var(--cms-text-muted)">
                        No permissions mapped yet.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
