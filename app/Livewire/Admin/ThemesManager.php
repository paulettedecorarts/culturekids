<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\CoercesNumericFormFields;
use App\Livewire\Concerns\UsesPortalContext;
use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\Theme;
use App\Services\WebPortalThemeService;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ThemesManager extends Component
{
    use CoercesNumericFormFields;
    use UsesPortalContext;
    use WithPagination;

    public $showModal = false;

    public $editing = false;

    public $showPreview = false;

    // Organization filter
    public $selectedOrgId = null;

    // Form fields
    public $selectedId;

    public $org_id;

    public $name;

    public $description;

    public $slug;

    // Colors
    public $primary = '#C44B2B';

    public $secondary = '#E8872A';

    public $accent = '#D4A017';

    public $success = '#4A7C59';

    public $warning = '#F2A84E';

    public $danger = '#9A3218';

    public $background = '#FAF6F0';

    public $surface = '#FFFFFF';

    public $text_primary = '#1A1208';

    public $text_secondary = '#6B5544';

    public $text_muted = '#9C8875';

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'slug' => 'required|alpha_dash|unique:themes,slug',
        'description' => 'nullable|max:500',
    ];

    public function mount()
    {
        $user = auth()->user();
        if ($user && $user->hasRole('org_admin') && ! $user->hasRole('super_admin')) {
            $this->selectedOrgId = (string) $user->organisation_id;
        }

        // Load default colors
        $defaults = Theme::defaultColors();
        foreach ($defaults as $key => $value) {
            $this->$key = $value;
        }
    }

    public function updatedName($value)
    {
        if (! $this->editing) {
            $this->slug = Str::slug($value, '_');
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = false;
        $this->org_id = $this->selectedOrgId; // Pre-fill with selected org
        $this->showModal = true;
    }

    public function edit($id)
    {
        $theme = Theme::findOrFail($id);

        if ($this->isOrgAdminOnly() && $theme->org_id === null) {
            session()->flash('error', 'Platform themes are read-only. Set one as your organization default, or customize your adopted copy below.');

            return;
        }

        $this->selectedId = $id;
        $this->org_id = $theme->org_id;
        $this->name = $theme->name;
        $this->slug = $theme->slug;
        $this->description = $theme->description;

        // Load colors
        foreach ($theme->colors as $key => $value) {
            $this->$key = $value;
        }

        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editing) {
            $rules['slug'] = 'required|alpha_dash|unique:themes,slug,'.$this->selectedId;
        }

        $this->normalizeNumericFormFields();
        $this->validate($rules);

        $user = auth()->user();
        $isOrgAdminOnly = $user && $user->hasRole('org_admin') && ! $user->hasRole('super_admin');
        if ($isOrgAdminOnly) {
            $this->org_id = $user->organisation_id;
        }

        $data = [
            'org_id' => $this->org_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'colors' => [
                'primary' => $this->primary,
                'secondary' => $this->secondary,
                'accent' => $this->accent,
                'success' => $this->success,
                'warning' => $this->warning,
                'danger' => $this->danger,
                'background' => $this->background,
                'surface' => $this->surface,
                'text_primary' => $this->text_primary,
                'text_secondary' => $this->text_secondary,
                'text_muted' => $this->text_muted,
            ],
        ];

        if ($this->editing) {
            $theme = Theme::findOrFail($this->selectedId);

            if ($isOrgAdminOnly && $theme->org_id === null) {
                session()->flash('error', 'Platform themes cannot be edited.');

                return;
            }

            $theme->update($data);

            AuditLog::record('UPDATE', "themes/{$theme->id}", [
                'theme_name' => $this->name,
            ]);

            session()->flash('message', 'Theme updated successfully.');
        } else {
            $theme = Theme::create($data);

            AuditLog::record('CREATE', "themes/{$theme->id}", [
                'theme_name' => $this->name,
            ]);

            session()->flash('message', 'Theme created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();

        $this->broadcastPortalThemeCssVars();
    }

    public function delete($id)
    {
        $user = auth()->user();
        $isOrgAdminOnly = $user && $user->hasRole('org_admin') && ! $user->hasRole('super_admin');
        $theme = Theme::findOrFail($id);

        if ($isOrgAdminOnly && $theme->org_id === null) {
            session()->flash('error', 'Platform themes cannot be deleted.');

            return;
        }

        if ($isOrgAdminOnly && (int) $theme->org_id !== (int) $user->organisation_id) {
            session()->flash('error', 'You are not allowed to delete this theme.');

            return;
        }

        if ($theme->is_default) {
            session()->flash('error', 'Cannot delete the default theme.');

            return;
        }

        AuditLog::record('DELETE', "themes/{$theme->id}", [
            'theme_name' => $theme->name,
        ]);

        $theme->delete();
        session()->flash('message', 'Theme removed successfully.');
    }

    public function setDefault($id)
    {
        $user = auth()->user();
        $isOrgAdminOnly = $user && $user->hasRole('org_admin') && ! $user->hasRole('super_admin');
        $theme = Theme::findOrFail($id);

        if ($isOrgAdminOnly) {
            if ($theme->org_id === null) {
                $theme = $this->resolveOrgThemeFromPlatform($theme, (int) $user->organisation_id);
            } elseif ((int) $theme->org_id !== (int) $user->organisation_id) {
                session()->flash('error', 'You are not allowed to modify this theme.');

                return;
            }
        }

        // Remove default from themes in the same org (or global if org_id is null)
        Theme::where('org_id', $theme->org_id)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        // Set new default
        $theme->is_default = true;
        $theme->save();

        AuditLog::record('UPDATE', "themes/{$theme->id}", [
            'action' => 'set_default',
            'theme_name' => $theme->name,
            'org_id' => $theme->org_id,
        ]);

        $orgName = $theme->org_id ? $theme->organisation->name : 'Global';
        session()->flash('message', "'{$theme->name}' set as default theme for {$orgName}.");

        $this->broadcastPortalThemeCssVars();
    }

    /**
     * Push resolved portal colours to the active layout (admin / CMS) without a full reload.
     */
    protected function broadcastPortalThemeCssVars(): void
    {
        $resolved = app(WebPortalThemeService::class)->forRequest(auth()->user());

        $this->dispatch('portal-theme-updated', cssVars: $resolved['css_variables_light'], cssVarsLight: $resolved['css_variables_light'], cssVarsDark: $resolved['css_variables_dark']);
    }

    /**
     * Org admin: clone a platform theme for this organisation (if not already adopted).
     */
    protected function resolveOrgThemeFromPlatform(Theme $platformTheme, int $orgId): Theme
    {
        if ($platformTheme->org_id !== null) {
            return $platformTheme;
        }

        $existing = Theme::query()
            ->where('org_id', $orgId)
            ->where('metadata->platform_theme_id', $platformTheme->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        $baseSlug = 'org_'.$orgId.'_from_'.$platformTheme->slug;
        $slug = $baseSlug;
        $suffix = 0;
        while (Theme::query()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $baseSlug.'_'.$suffix;
        }

        return Theme::create([
            'org_id' => $orgId,
            'name' => $platformTheme->name,
            'slug' => $slug,
            'description' => $platformTheme->description,
            'is_default' => false,
            'is_active' => true,
            'colors' => $platformTheme->colors,
            'typography' => $platformTheme->typography,
            'spacing' => $platformTheme->spacing,
            'borders' => $platformTheme->borders,
            'metadata' => array_merge($platformTheme->metadata ?? [], [
                'platform_theme_id' => $platformTheme->id,
                'adopted_from_platform' => true,
            ]),
        ]);
    }

    protected function isOrgAdminOnly(): bool
    {
        $user = auth()->user();

        return $user && $user->hasRole('org_admin') && ! $user->hasRole('super_admin');
    }

    public function applyPreset($preset)
    {
        $presets = $this->getPresets();
        if (isset($presets[$preset])) {
            foreach ($presets[$preset]['colors'] as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    private function getPresets()
    {
        return [
            'savanna' => [
                'name' => 'Savanna Sunset',
                'colors' => [
                    'primary' => '#C44B2B',
                    'secondary' => '#E8872A',
                    'accent' => '#D4A017',
                    'success' => '#4A7C59',
                    'warning' => '#F2A84E',
                    'danger' => '#9A3218',
                    'background' => '#FAF6F0',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1A1208',
                    'text_secondary' => '#6B5544',
                    'text_muted' => '#9C8875',
                ],
            ],
            'ocean' => [
                'name' => 'Ocean Breeze',
                'colors' => [
                    'primary' => '#2E4D8A',
                    'secondary' => '#4A72C4',
                    'accent' => '#6FA882',
                    'success' => '#4A7C59',
                    'warning' => '#F2A84E',
                    'danger' => '#C44B2B',
                    'background' => '#F0F4F8',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1E2D4A',
                    'text_secondary' => '#475569',
                    'text_muted' => '#94A3B8',
                ],
            ],
            'forest' => [
                'name' => 'Forest Green',
                'colors' => [
                    'primary' => '#4A7C59',
                    'secondary' => '#6FA882',
                    'accent' => '#B8D9C6',
                    'success' => '#4A7C59',
                    'warning' => '#E8872A',
                    'danger' => '#C44B2B',
                    'background' => '#EBF5EE',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#1A3A2A',
                    'text_secondary' => '#2D5A3D',
                    'text_muted' => '#6B8B7A',
                ],
            ],
            'sunset' => [
                'name' => 'Desert Sunset',
                'colors' => [
                    'primary' => '#E8872A',
                    'secondary' => '#F2A84E',
                    'accent' => '#FDF0DE',
                    'success' => '#6FA882',
                    'warning' => '#D4A017',
                    'danger' => '#C44B2B',
                    'background' => '#FFF8F0',
                    'surface' => '#FFFFFF',
                    'text_primary' => '#3A2010',
                    'text_secondary' => '#6B4423',
                    'text_muted' => '#9C7A5A',
                ],
            ],
        ];
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->org_id = $this->selectedOrgId;
        $this->name = '';
        $this->slug = '';
        $this->description = '';

        // Reset to defaults
        $defaults = Theme::defaultColors();
        foreach ($defaults as $key => $value) {
            $this->$key = $value;
        }

        $this->resetErrorBag();
    }

    public function render()
    {
        $user = auth()->user();
        $isOrgAdminOnly = $this->isOrgAdminOnly();
        $orgId = $user?->organisation_id;

        $platformThemes = collect();
        $orgThemes = null;
        $themes = null;
        $activePlatformThemeIds = [];

        if ($isOrgAdminOnly) {
            $platformThemes = Theme::query()
                ->whereNull('org_id')
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get();

            $orgThemes = Theme::with('organisation')
                ->where('org_id', $orgId)
                ->latest()
                ->paginate(12);

            $orgDefault = Theme::query()
                ->where('org_id', $orgId)
                ->where('is_default', true)
                ->first();

            $activePlatformThemeIds = [];
            if ($orgDefault && is_array($orgDefault->metadata) && isset($orgDefault->metadata['platform_theme_id'])) {
                $activePlatformThemeIds[] = (int) $orgDefault->metadata['platform_theme_id'];
            }
        } else {
            $query = Theme::with('organisation')->latest();

            if ($this->selectedOrgId === 'global') {
                $query->whereNull('org_id');
            } elseif ($this->selectedOrgId) {
                $query->where('org_id', $this->selectedOrgId);
            }

            $themes = $query->paginate(12);
        }

        $presets = $this->getPresets();
        $organisations = $isOrgAdminOnly
            ? Organisation::where('id', $orgId)->orderBy('name')->get()
            : Organisation::orderBy('name')->get();

        return view('livewire.admin.themes-manager', [
            'themes' => $themes,
            'platformThemes' => $platformThemes,
            'orgThemes' => $orgThemes,
            'activePlatformThemeIds' => $activePlatformThemeIds,
            'presets' => $presets,
            'organisations' => $organisations,
            'isOrgAdminOnly' => $isOrgAdminOnly,
        ])->layout($this->portalLayout());
    }
}
