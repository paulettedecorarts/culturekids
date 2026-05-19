# Super Admin — Submit button loading states

Livewire forms in the Super Admin portal use a shared Blade component for consistent **disabled + loading** feedback while requests run.

## Component

**Path:** `resources/views/components/livewire-submit-button.blade.php`

**Usage:**

```blade
{{-- Form submit (wire:submit on parent <form>) --}}
<x-livewire-submit-button target="save" variant="block">
    Save changes
</x-livewire-submit-button>

{{-- Standalone action (wire:click) --}}
<x-livewire-submit-button
    type="button"
    wire:click="saveTribeAccess"
    target="saveTribeAccess"
    variant="md"
    loading="{{ __('Saving…') }}"
>
    Save tribe access
</x-livewire-submit-button>
```

## Props

| Prop | Default | Description |
|------|---------|-------------|
| `target` | *(required)* | Livewire `wire:target` — method name, or comma-separated list (e.g. `saveSong,media_file`) |
| `loading` | `Saving…` | Text shown while the action runs |
| `type` | `submit` | `submit` or `button` |
| `variant` | `primary` | Style preset (see below) |

Additional HTML attributes (`wire:click`, `wire:confirm`, `style`, `class`) are merged onto the `<button>`.

## Variants

| Variant | Use case |
|---------|----------|
| `primary` | Header actions (user form, tribe form) |
| `block` | Full-width modal / form footer |
| `md` | Org detail section saves |
| `success-sm` | Green “Save” in content detail headers |
| `sm` | Small inline actions (impersonate, maintenance) |

Styles live in `resources/css/admin-content.css` under `.lw-submit-btn*`.

## Behaviour

- Button is **disabled** while the targeted Livewire action runs (`wire:loading.attr="disabled"`).
- Label is swapped for a **spinner + loading text** (`wire:loading.flex` / `wire:loading.remove`).
- Prevents double-clicks on slow saves (uploads, maintenance mode, impersonation).

## Screens already using the component

- Dashboard (maintenance toggle)
- Users (create/edit)
- Organizations (list modal, create, detail plan/tribes)
- Themes, modules registry, tribes, clans, translations (list + full-page form)
- Age profiles, languages, activities, songs
- Panel editor (vocabulary tag)
- Impersonate user

**Story form** keeps its own `$isSaving` state (file uploads) — intentional exception.

## Adding to a new admin screen

1. Use `wire:submit.prevent="yourMethod"` on the form **or** `wire:click="yourMethod"` on the button.
2. Set `target` to that method name (must match exactly).
3. Pick a `variant` and optional custom `loading` string.
