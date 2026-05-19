# Organisation modules

Product modules are **fixed feature flags** (not created in admin). Each of the 12 content types has its own module key, plus 3 platform modules. Definitions and mappings live in **`config/modules.php`**.

## Canonical module keys (15)

### Content (12) — matches `OrganisationContentDecision::ALL_TYPES`

| Key | Content type | Name |
|-----|--------------|------|
| `stories` | `story` | Stories |
| `songs` | `song` | Songs & Audio |
| `flashcards` | `flashcard` | Flashcards |
| `puzzles` | `puzzle` | Puzzles |
| `drawings` | `drawing` | Drawings |
| `colouring` | `colouring` | Colouring |
| `language_activities` | `language` | Language Activities |
| `games` | `game` | Games |
| `mazes` | `maze` | Mazes |
| `spot_difference` | `spot_difference` | Spot the Difference |
| `word_searches` | `word_search` | Word Searches |
| `culture_activities` | `culture` | Culture Activities |

### Platform (3)

| Key | Purpose |
|-----|---------|
| `offline_bundles` | `.ckb` downloads (`/api/v1/offline/*`) |
| `theme_engine` | Org branding (`GET /api/v1/organisation/theme`) |
| `kiosk_mode` | Classroom tablet mode (mobile) |

Seed / refresh:

```bash
php artisan db:seed --class=ModuleSeeder
```

`ModuleSeeder` runs from `DatabaseSeeder` on fresh installs. Legacy module key `comics` is removed in favour of `stories`.

## Configuration

| File | Role |
|------|------|
| `config/modules.php` | `definitions`, `content_types`, `activity_types`, `age_profile_modules`, `age_profile_activity_types` |
| `OrganisationModuleResolver` | Runtime enable/disable per user/org |
| `ModuleSeeder` | Writes rows to `modules` table |

## Resolution rules

1. **Global** — `modules.is_enabled` must be true.
2. **Per organisation** — `module_organisation.is_enabled`; no pivot row = **enabled** (opt-out).
3. **B2C users** — all globally enabled modules apply.

Helpers: `isEnabledForUser()`, `isContentTypeAllowed()`, `isActivityTypeAllowed()`.

## Admin UI

| Route | Purpose |
|-------|---------|
| `/admin` | Dashboard quick global toggles |
| `/admin/modules` | Full global on/off |
| `/admin/organisations/{id}` | Per-org overrides |

There is **no** module registry form — new modules are added in `config/modules.php` when shipping a feature.

## Mobile API

```
GET /api/v1/organisation/modules
```

Story content uses module key **`stories`** (API path remains `/api/v1/comics`).

### Web enforcement (Phases 3–4)

| Area | Behaviour when module off |
|------|---------------------------|
| Org review queue / approved content | Hidden from lists; approve/reject ignored |
| Teacher library / story reader / print center | Excluded from catalog and queries |

### Mobile API enforcement (Phase 2)

| Area | Behaviour when module off |
|------|---------------------------|
| `/comics`, `/reading-progress` | 403 (`stories`) |
| `/songs` | 403 (`songs`) |
| `/activities` | Hidden; `?type=` → 403 |
| `/offline/*` | 403 if `offline_bundles` off; manifest omits disabled content types |
| `/organisation/theme` | Platform default + `theme_engine_enabled: false` |

## Age profile bridging (Phase 5)

Child age rules live in `age_profiles.content_access_rules.modules` (`stories`, `puzzle`, `flashcard`, …). Organisation modules use keys like `stories`, `puzzles`, `flashcards`.

Mappings in `config/modules.php`:

- `age_profile_modules` — age string → organisation module key  
- `age_profile_activity_types` — age string → `activities.type` (stories use the comics API, not activities)

`GET /api/v1/age-profiles` (authenticated) returns each profile with:

| Field | Meaning |
|-------|---------|
| `modules` | Age-band allow list (unchanged) |
| `effective_modules` | Subset after organisation licence |
| `effective_organisation_module_keys` | Org module keys that apply |
| `effective_activity_types` | DB activity types allowed for this child context |

Expo should gate UI with **`effective_modules`** (or `effective_organisation_module_keys`), not `modules` alone.

## See also

- [module-catalog.md](./module-catalog.md) — full matrix + implementation phases  
- [system-workflows-reference.md](./system-workflows-reference.md)  
- [mobile-api-theming.md](./mobile-api-theming.md)
