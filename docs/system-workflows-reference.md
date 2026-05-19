# System workflows reference (spec → Laravel)

Maps sections of `doc/Paulette Culture Kids — Laravel · MySQL · Expo React Native System Workflows.html` to this repository. Use this when aligning product copy, mock UI, or mobile contracts with the webapp.

## Document locations

| Artifact | Path |
|----------|------|
| Product HTML spec | `doc/Paulette Culture Kids — Laravel · MySQL · Expo React Native System Workflows.html` |
| Super Admin analysis | `docs/super-admin-module.md` |
| Modules (this app) | `docs/modules.md` |
| Mobile theme | `docs/mobile-api-theming.md` |
| Admin submit UX | `docs/admin-ui-submit-buttons.md` |

## Super Admin (S2 / God Mode)

| Spec area | Implementation |
|-----------|----------------|
| Platform stats cards | `PlatformStatsService`, `livewire/admin/dashboard` |
| Engagement chart / top stories | `PlatformAnalyticsService`, `partials/engagement-analytics` |
| Organisation table | `Dashboard` → `Organisation` list + Manage links |
| Global module toggles | `Module` + `Dashboard::toggleGlobalModule`, `/admin/modules` |
| Maintenance mode | `Dashboard::toggleMaintenance`, `bootstrap/app.php` exceptions |
| Dedicated subdomain `admin.culturekids.app` | **Not implemented** (use `/admin` on main app) |

## Modules (schema difference)

| Spec | This app |
|------|----------|
| `organisations.modules` JSON | `modules` + `module_organisation` pivot |
| Six fixed keys in mock UI | `ModuleSeeder` + `OrganisationModuleResolver::canonicalDefinitions()` |
| Mobile reads org modules | `GET /api/v1/organisation/modules` |

## Theming

| Spec | This app |
|------|----------|
| `theme_configs` table (HTML schema) | `themes` table + `organisations.theme` JSON merge |
| Mobile brand fetch | `GET /api/v1/organisation/theme`, `OrganisationThemeResolver` |

## Content & CMS (selected routes)

| Domain | Admin (web) | Mobile API |
|--------|-------------|--------------|
| Comics / stories | `/admin/comics`, story activities | `/api/v1/comics` (module: `comics`) |
| Songs | `/admin/songs` | `/api/v1/songs` (module: `songs`) |
| Flashcards | `/admin/flashcards` | `/api/v1/activities?type=flashcard` |
| Tribes | `/admin/tribes` | `/api/v1/tribes` |
| Offline `.ckb` | story packs / bundles admin | `/api/v1/offline/*` (module: `offline_bundles`) |
| Age profiles | `/admin/age-profiles` | `/api/v1/age-profiles` |
| Org content review | review queue Livewire | — |

## Auth & roles

| Role | Typical access |
|------|----------------|
| `super_admin` | Full `/admin`, `PUT .../admin/organisations/{id}/modules` |
| `org_admin` | Organisation-scoped admin (where implemented) |
| `cms_editor` | Content CRUD |
| `teacher` / `parent` | Sanctum mobile API |

Seeded super admin: `admin@culturekids.app` / `password` (`DatabaseSeeder`).

## API prefix

All mobile routes are under **`/api/v1/`** (see `bootstrap/app.php` / `RouteServiceProvider`). Prefer this prefix in new tests and Expo clients.

## Deferred / not in webapp

- Badge awards dedicated table (use progress/events until product defines)
- Kiosk shell (mobile-only; module flag only)
- `admin.culturekids.app` DNS
