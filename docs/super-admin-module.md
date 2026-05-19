# Super Admin Module — Spec vs Implementation

Analysis of the **Super Admin Flow — Laravel God Mode** design (steps S1–S5 + dashboard mockup) against the current codebase.

**Overall coverage: ~65–70%** of the described Super Admin vision.

The platform shell, org management, impersonation, age profiles, themes, and content CMS are largely in place. Gaps are mainly dashboard metrics, dedicated subdomain/middleware story, theme API for mobile, and some polish (maintenance mode, badges on dashboard).

---

## Step-by-step comparison

| Spec step | Described | What exists | Coverage |
|-----------|-----------|-------------|----------|
| **S1** Access / God Mode | `admin.culturekids.app`, `SuperAdminMiddleware`, `config(['scoping.org_id' => null])` | `/admin/*`, middleware `role:super_admin` + `log.admin`. **No** `SuperAdminMiddleware` or `scopingSetup.org_id`. No global Eloquent org scope — admin queries are effectively platform-wide. Subdomain is deploy/DNS only. | **~70%** |
| **S2** Orgs & subscriptions | CRUD, suspend, Free/School/Enterprise, per-org modules, tribe selection | `OrganizationsManager`, `OrganizationCreate`, `OrganizationDetail`: plan tiers, active/inactive toggle, per-org module pivots, `allowed_tribe_ids` in settings. API: `PUT .../organisations/{id}/modules`. | **~90%** |
| **S3** Age profiles & UI rules | Bands 2–3…5–6, complexity/unlock rules, `age_profiles`, mobile API | `AgeCategories` + `AgeProfileDetailPage` (min/max age, ui_scale, reading_level, activity_complexity, content_access_rules, ui_features). API: `GET /api/v1/age-profiles` (Sanctum). Child profiles auto-assign age from DOB (tests). | **~85%** |
| **S4** Theme engine | No-code tokens per org, `theme_configs`, JSON API for app | `ThemesManager` + `themes` table; `GET /api/v1/organisation/theme` via `OrganisationThemeResolver`. `organisations.theme` JSON merged as overrides. | **~85%** |
| **S5** Impersonation | Temp token, audit with `impersonator_id` | Session-based `Auth::login()` + `impersonator_id` on `audit_logs` — **not** Sanctum `createToken('impersonation')`. UI at `/admin/impersonate`, stop via `POST admin/stop-impersonation`. Portal isolation enforced. | **~80%** |

---

## S1: Access Super Admin Panel

**Spec**

- Hosted on `admin.culturekids.app`
- `SuperAdminMiddleware` sets `config(['scoping.org_id' => null])` for RBAC bypass

**Implementation**

- Routes under `/admin/*` in `routes/web.php` (~lines 72–176)
- Middleware: `auth`, `role:super_admin`, `log.admin`
- Layout: `layouts/admin.blade.php`, theme `data-sa-theme`, `admin-content.css`
- **Not found:** `SuperAdminMiddleware.php`, `scoping.org_id` config key
- Multi-tenant bypass is implicit: no global `org_id` scope on models; admin Livewire components query without org filter

**Gap:** Subdomain and explicit scoping middleware are deployment/design-doc only unless added later.

---

## S2: Manage Organisations & Subscriptions

**Spec**

- Create, edit, suspend organisations
- Plan tiers: Free, School, Enterprise
- Per-org module toggles and tribe (“content”) selection

**Implementation**

| Feature | Location |
|---------|----------|
| List / search orgs | `App\Livewire\Admin\OrganizationsManager` |
| Create org | `App\Livewire\Admin\OrganizationCreate` |
| Detail: plan, status, modules, tribes | `App\Livewire\Admin\OrganizationDetail` |
| Global module catalog | `App\Livewire\Admin\ModuleToggles` → `/admin/modules` |
| API module update | `App\Http\Controllers\Api\OrganisationModuleAdminController` |

Plans: `free`, `school`, `enterprise`. Status via active/inactive toggle (suspend equivalent). Tribe allow-list stored in organisation `settings` (`allowed_tribe_ids`).

---

## S3: Age Profiles & UI Rules

**Spec**

- Age bands (2–3, 3–4, 4–5, 5–6)
- UI modes, difficulty ceilings, activity unlock rules
- Mobile fetches via Sanctum API on startup

**Implementation**

- Table: `age_profiles`
- Admin: `AgeCategories`, `AgeProfileDetailPage` (fields include `ui_scale`, `touch_target_px`, `reading_level`, `activity_complexity`, `content_access_rules`, `ui_features`, modules CSV)
- API: `GET /api/v1/age-profiles` — `App\Http\Controllers\Api\AgeProfileController`
- Tests: `tests/Feature/AgeProfileFunctionalityTest.php`

---

## S4: Theme Engine & Branding

**Spec**

- No-code theme builder in Blade admin
- `theme_configs` table; API returns JSON for app runtime

**Implementation**

- `App\Livewire\Admin\ThemesManager` → `/admin/themes`
- `App\Models\Theme` — per-organisation color tokens, `org_id`
- Organisation may also store theme JSON on `organisations.theme`
- **Gap:** No dedicated `theme_configs` table name; no theme route in `routes/api.php` for mobile consumption (verify mobile app separately)

---

## S5: User Impersonation

**Spec**

- Temporary impersonation token (`createToken('impersonation', ['impersonated'])`)
- All actions logged with `impersonator_id`

**Implementation**

| Piece | Location |
|-------|----------|
| Start impersonation | `App\Livewire\Admin\ImpersonateUser` |
| Stop | `App\Http\Controllers\Admin\ImpersonationController@stop` |
| Session keys | `impersonating`, `impersonator_id`, `original_user_id` |
| Audit | `App\Models\AuditLog` — `impersonator_id` column |
| Portal guard | `App\Http\Middleware\EnsurePortalRoleIsolation` — super_admin blocked from teacher/org/cms unless impersonating |
| Action logging | `App\Http\Middleware\LogSuperAdminActions` |

Cannot impersonate self or other super admins.

---

## Dashboard mockup vs live dashboard

| Mockup widget | Live (`admin.dashboard`) | Match? |
|---------------|--------------------------|--------|
| Active Children (e.g. 2,847) | Total **users** (not children-only) | No |
| Organisations count | Organisation count | Yes |
| Published Stories (tribes) | **Activities** table count | No |
| Badges earned (7 days) | Not shown | No |
| Active org table + Manage | Table present; some actions are placeholders | Partial |
| “God Mode Active” badge | “SUPER ADMIN” style badge | Similar |
| Module toggles on dashboard | Live global toggles + link to `/admin/modules`; `ModuleSeeder` for six canonical keys | Done |
| Mobile org modules API | `GET /api/v1/organisation/modules` + content route gating | Done — see [modules.md](./modules.md) |

**Dashboard vs mockup: ~40–50%.**

**Richer analytics elsewhere:** `/admin/analytics` (`AnalyticsManager`) — active pupils, completions, avg stars, weekly engagement chart (real queries).

---

## Extra capabilities (beyond S1–S5)

Built in admin but not all listed in the God Mode sidebar mockup:

- Content CMS breadth: stories/comics, songs, activities, puzzles, games, tribes, clans, languages, drawings, story packs, translations, assets
- Users management
- Permissions viewer (Spatie; largely read-only UI)
- Audit logs UI (`/admin/audit-logs`)
- Impersonation into editor / org admin / teacher portals

Many routes exist under `/admin` that are **not** all linked from `resources/views/layouts/partials/admin-sidebar.blade.php`.

---

## Workflow doc items (not in S1–S5 diagram)

| Feature | Status |
|---------|--------|
| Offline bundle builder | Editor/CMS routes; not prominent in super-admin nav |
| API token management (super-admin UI) | Sanctum for mobile users only |
| MySQL / health monitor on dashboard | `/health` exists; not a God Mode widget |
| Maintenance mode toggle | Dashboard control is placeholder (`toast` only) |

---

## Coverage summary

```
S1 Access & isolation     ████████░░  ~75%
S2 Organisations          █████████░  ~90%
S3 Age profiles           █████████░  ~85%
S4 Themes                 ██████░░░░  ~55%
S5 Impersonation          ████████░░  ~80%
Dashboard (mockup)        █████░░░░░  ~45%
Content / CMS breadth     █████████░  ~90% (beyond spec)
```

---

## Key file paths

| Area | Path |
|------|------|
| Admin routes | `routes/web.php` (admin group) |
| Livewire admin | `app/Livewire/Admin/*` |
| Layout / sidebar | `resources/views/layouts/admin.blade.php`, `layouts/partials/admin-sidebar.blade.php` |
| Middleware | `EnsurePortalRoleIsolation`, `LogSuperAdminActions` |
| Seeded super admin | `DatabaseSeeder` — `admin@culturekids.app` / `password` |
| Age profiles API | `routes/api.php` → `AgeProfileController` |
| Organisation theme API | `routes/api.php` → `OrganisationThemeController` |
| Theme resolver | `app/Services/OrganisationThemeResolver.php` |
| Org modules API | `OrganisationModuleAdminController` |

---

## Recommended follow-ups

### Phase 1 (done)

- **`App\Services\Admin\PlatformStatsService`** — shared platform metrics for the dashboard.
- **Dashboard** — Active children, organisations, published stories, learning completions (7d); org Manage links; interactive module toggles.
- **Modules** — `ModuleSeeder`, `GET /api/v1/organisation/modules`, API enforcement on comics/songs/activities/offline — [modules.md](./modules.md), [system-workflows-reference.md](./system-workflows-reference.md).
- **Sidebar** — Drawings, games, puzzles, mazes, spot-difference, word searches, culture/language activities, story packs, assets, translations.
- **Maintenance mode** — Livewire toggle via `artisan down` / `up`; admin routes exempt in `bootstrap/app.php`.

### Phase 2 (done)

- **`GET /api/v1/organisation/theme`** (Sanctum) — Returns resolved theme for the authenticated user’s organisation.
- **`App\Services\OrganisationThemeResolver`** — Merges default `themes` row (per org or global), then `organisations.theme` JSON overrides.
- **Tests:** `tests/Feature/Api/OrganisationThemeApiTest.php`
- **App developer guide:** [mobile-api-theming.md](./mobile-api-theming.md)

Response shape:

```json
{
  "theme": {
    "source": "organisation_theme|organisation_override|platform_default|platform_theme",
    "organisation_id": 1,
    "theme_id": 5,
    "name": "Sunrise Brand",
    "slug": "sunrise_brand",
    "logo_url": null,
    "colors": { "primary": "#2E4D8A", ... },
    "typography": null,
    "spacing": null,
    "borders": null,
    "metadata": {}
  }
}
```

### Phase 3 (webapp — done)

- **`App\Services\Admin\PlatformAnalyticsService`** — shared engagement metrics (pupils, completions, stars, 7-day chart, top stories).
- **Global dashboard** — includes engagement section + link to full analytics page.
- **Analytics page** — refactored to use the same partial (`engagement-analytics.blade.php`).

### Phase 4+ (remaining)

1. **Subdomain** — Configure `admin.culturekids.app` in Coolify/DNS *(deferred)*.
2. **Scoping middleware (optional)** — Only if a global tenant scope is introduced.
3. **Dedicated badge metric** — Optional `badge_awards` table if product needs audit trail beyond completion counts.
4. **Submit buttons on CMS / Teacher portals** — reuse `x-livewire-submit-button` outside Super Admin if desired.

---

*Last updated: May 2026 — based on codebase review against God Mode workflow mockup.*
