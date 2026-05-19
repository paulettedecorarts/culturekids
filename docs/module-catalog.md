# Module catalog — 12 activities vs current & proposed modules

Reference for organisation **feature flags** (`modules` table). The **12 activities** are the content types in `OrganisationContentDecision::ALL_TYPES` — what org admins approve and teachers browse.

**Not in this table:** platform-only areas (users, tribes directory, audit logs, auth). **Separate system:** age-profile `content_access_rules.modules` (child age gating) — see [modules.md](./modules.md).

---

## Summary

| Layer | Count | Notes |
|-------|------:|-------|
| **Content activities (org review)** | 12 | One proposed module each |
| **Platform modules (today)** | 6 | Spec + offline / theme / kiosk |
| **Mapped in code today** | 12 of 12 | `config/modules.php` + `OrganisationModuleResolver` (Phase 1) |

---

## Master table: 12 activities

| # | Activity (review type) | Admin label | DB / sync (`activities.type` or model) | **Current module** (seeded) | **Proposed module key** | Who is affected when OFF |
|---|------------------------|-------------|----------------------------------------|----------------------------|-------------------------|-------------------------|
| 1 | `story` | Story | `Comic` + `activities.type = story` | `comics` | `stories` *(or keep `comics`)* | **Parent / child (app):** no comics API, no story in offline bundle. **Teacher:** story library & reader hidden. **Org admin:** story review/approved rows irrelevant for pupils. **CMS editor:** can still author; school users don’t see. |
| 2 | `song` | Song | `Song` + `activities.type = song` | `songs` | `songs` | **Parent / child:** no `/songs`, songs stripped from offline packs. **Teacher:** song library entries blocked. **Org admin:** song approval queue unused for learners. |
| 3 | `flashcard` | Flashcard | `activities.type = flashcard` | `flashcards` | `flashcards` | **Parent / child:** flashcards filtered from `/activities`. **Teacher:** flashcard library blocked. **Org admin:** flashcard review unused for learners. |
| 4 | `puzzle` | Puzzle | `activities.type = puzzle` (+ `Puzzle` CMS) | *(none)* | `puzzles` | **Parent / child:** puzzles hidden from activities API (when mapped). **Teacher:** puzzle library + printables. **Org admin:** puzzle review. |
| 5 | `drawing` | Drawing | `Drawing` → `activities.type = drawing_kit` | *(none)* | `drawings` | **Parent / child:** drawing activities hidden (when API exists). **Teacher:** drawing library. **Org admin:** drawing review. |
| 6 | `colouring` | Colouring | `Drawing` (colouring flag) → `drawing_kit` | *(none)* | `colouring` | **Parent / child:** colouring hidden. **Teacher:** colouring library route. **Org admin:** colouring review (separate from drawing). |
| 7 | `language` | Language | `LanguageActivity` → `activities.type = vocab_pack` | *(none)* | `language_activities` | **Parent / child:** vocab/language activities hidden. **Teacher:** language-activities library. **Org admin:** language review. |
| 8 | `game` | Game | `Game` → `activities.type = game` | *(none)* | `games` | **Parent / child:** games hidden. **Teacher:** games library. **Org admin:** game review. |
| 9 | `maze` | Maze | `Maze` → `activities.type = maze` | *(none)* | `mazes` | **Parent / child:** mazes hidden. **Teacher:** mazes library. **Org admin:** maze review. |
| 10 | `spot_difference` | Spot the Difference | `SpotDifference` → `activities.type = spot_difference` | *(none)* | `spot_difference` | **Parent / child:** hidden. **Teacher:** spot-difference library. **Org admin:** review. |
| 11 | `word_search` | Word Search | `WordSearch` → `activities.type = word_search` | *(none)* | `word_searches` | **Parent / child:** hidden. **Teacher:** word-search library. **Org admin:** review. |
| 12 | `culture` | Culture | `CultureActivity` → `activities.type = culture` | *(none)* | `culture_activities` | **Parent / child:** hidden. **Teacher:** culture-activities library. **Org admin:** review. |

**Super Admin** is affected on every row only for **global** toggle (all schools) and dashboard preview — not blocked from CMS.

---

## Platform modules (not one of the 12 activities)

These are product capabilities, not a content type in the review queue.

| Module key (today) | Purpose | Who is affected when OFF |
|--------------------|---------|---------------------------|
| `offline_bundles` | `.ckb` downloads, tribe bundles, parent downloaded packs | **Parent / child:** all `/api/v1/offline/*` return 403. **Teacher:** offline/download flows in app. Content may still exist online. |
| `theme_engine` | Org colours, logo, fonts via `GET /organisation/theme` | **Parent / child:** app should use default branding (endpoint still works; UI may hide custom theme). **Org admin:** theme manager less useful for end users. |
| `kiosk_mode` | Locked classroom tablet experience | **Teacher / school IT:** kiosk UX in Expo only (flag in modules list). **Parent:** N/A. |

---

## Current vs proposed module list

### Today — 6 seeded modules (`ModuleSeeder`)

| Key | Covers which of the 12? |
|-----|-------------------------|
| `comics` | #1 Story only |
| `songs` | #2 Song only |
| `flashcards` | #3 Flashcard only |
| `offline_bundles` | Platform (downloads all types) |
| `theme_engine` | Platform (branding) |
| `kiosk_mode` | Platform (classroom mode) |

### Proposed — 12 activity modules + 3 platform (15 total)

| Key | Activity # |
|-----|------------|
| `stories` or `comics` | 1 |
| `songs` | 2 |
| `flashcards` | 3 |
| `puzzles` | 4 |
| `drawings` | 5 |
| `colouring` | 6 |
| `language_activities` | 7 |
| `games` | 8 |
| `mazes` | 9 |
| `spot_difference` | 10 |
| `word_searches` | 11 |
| `culture_activities` | 12 |
| `offline_bundles` | Platform |
| `theme_engine` | Platform |
| `kiosk_mode` | Platform |

---

## Who is affected — roles (reference)

| Role | How modules apply |
|------|-------------------|
| **Super Admin** | Global on/off on `/admin/modules` and dashboard; not blocked from creating content. |
| **CMS Editor** | Creates content for the platform; org modules don’t stop authoring. |
| **Org Admin** | Per-org toggles on organisation detail; **review queue** and **approved content** for each of the 12 types; themes if `theme_engine` on. |
| **Teacher** | **Teacher hub** library routes per type (`teacher.library:*`); tribes explorer; print center / worksheets (often tied to puzzles). |
| **Parent** | Sanctum mobile API + offline packs; sees only modules enabled for their `organisation_id`. B2C (`organisation_id` null) uses globally enabled modules. |
| **Child** | Indirect: parent account + **age profile** rules (`stories`, `puzzle`, etc. in `age_profiles`) — second layer on top of org modules. |

---

## Code touchpoints (for implementation)

| Concern | Location |
|---------|----------|
| 12 review types | `App\Models\OrganisationContentDecision::ALL_TYPES` |
| Activity sync types | `activities.type`: `story`, `song`, `flashcard`, `puzzle`, `drawing_kit`, `vocab_pack`, `game`, `maze`, `spot_difference`, `word_search`, `culture` |
| Current module → activity map | `OrganisationModuleResolver::activityTypeToModuleKey()` (3 entries) |
| Seeded modules | `OrganisationModuleResolver::canonicalDefinitions()` + `ModuleSeeder` |
| Mobile gating (partial) | `ComicController`, `SongController`, `ActivityController`, `OfflineBundleController` |
| Org per-type approval | `OrganisationContentReviewService`, `cms/admin/review` |
| Teacher per-type library | `routes/web.php` `teacher.library:*` |

---

## Age-profile names (related, not org modules)

Child-level allow list in `age_profiles.content_access_rules.modules` uses different strings, e.g. `stories`, `songs`, `puzzle`, `vocab_pack`, `flashcard`, `worksheet`, `game`. When implementing 12 org modules, document a mapping from org module keys → age-profile keys.

---

## Implementation plan

Goal: **15 modules** (12 content + 3 platform), one key per activity, enforced consistently. No “create module” admin form — only toggles.

### Phase 1 — Catalog & mapping (backend core) ✅ Done

- **`config/modules.php`** — 15 definitions + content/activity/age mappings  
- **`stories`** module key (replaces legacy `comics`) + migration `2026_05_19_120000_rename_comics_module_key_to_stories`  
- **`ModuleSeeder`** — seeds all 15, deletes `comics`  
- **`OrganisationModuleResolver`** — `moduleKeyForContentType()`, `isContentTypeAllowed()`, full activity map  
- **Module Registry removed** — route, sidebar, Livewire component  
- **Tests** — `OrganisationModuleResolverTest`, updated `OrganisationModuleApiTest`  
- **API gating** — `ComicController` uses `stories` module key  

### Phase 2 — Mobile API enforcement ✅ Done

- **Activities** — full type map; `?type=` returns 403 when module off; index filters by module  
- **Offline bundles** — manifests/assets/child content omit disabled stories, songs, activities  
- **Comic download** — requires `stories` + `offline_bundles`  
- **Reading progress** — requires `stories`  
- **Theme** — when `theme_engine` off, returns platform default + `theme_engine_enabled: false`  
- **Tests** — `OrganisationModuleEnforcementTest`

### Phase 3 — Organisation admin (web) ✅ Done

- `OrganisationContentReviewService` — pending/approved lists filtered; approve/reject no-op when module off  
- `ReviewQueue` / `ApprovedContent` — inherit filtering via service  

### Phase 4 — Teacher hub (web) ✅ Done

- `TeacherApprovedCatalogService` — catalog items respect org modules  
- `TeacherCatalogScope` — comics/songs queries + `userCanView*` checks  
- `TeacherPrintScope` — printable activities filtered by activity module map  
- `EnsureTeacherLibraryAccess` — via `userCanViewItem` on filtered catalog  

**Tests:** `OrganisationModuleWebScopeTest`

### Phase 5 — Age profile bridging ✅ Done

- `config/modules.php` — `age_profile_activity_types`  
- `OrganisationModuleResolver::formatAgeProfileForApi()` — effective_* fields on age profiles API  
- `AgeCategoryPolicyService` — comics/songs/activities filtered with org + age rules  
- **Tests:** `AgeProfileModuleBridgeTest`, extended `AgeCategoryPolicyServiceTest`
- **Expo:** [modules.md — Expo / React Native](./modules.md#expo--react-native--module-and-content-gating)

---

### Phase 2 — Mobile API enforcement

| Surface | Change |
|---------|--------|
| `GET /organisation/modules` | Returns all 15; mobile hides nav by `enabled_keys` |
| `ComicController` | Module `comics` (or `stories`) |
| `SongController` | `songs` |
| `ActivityController` | Full `activityTypeToModuleKey()` map |
| `OfflineBundleController` | Filter manifest: omit comics/songs/activities whose module is off; keep route gated by `offline_bundles` |
| `OrganisationThemeController` | Optional: 403 if `theme_engine` off, or return platform default (product choice) |

**Deliverable:** `OrganisationModuleApiTest` + per-type 403 tests.

---

### Phase 3 — Organisation admin (web)

| Surface | Change |
|---------|--------|
| `OrganizationDetail` | Lists all 15 modules; existing `toggleOrgModule` unchanged |
| `OrganisationContentReviewService` / `ReviewQueue` | Only show pending items whose `content_type` module is enabled for that org |
| `ApprovedContent` | Same filter on lists |
| `PUT .../admin/organisations/{id}/modules` | Accept all module ids (already generic) |

**Deliverable:** Org admin does not review puzzles if `puzzles` is off.

---

### Phase 4 — Teacher hub (web)

| Surface | Change |
|---------|--------|
| `TeacherApprovedCatalogService` | Skip catalog rows when org module off |
| `TribesExplorer` / story library | Respect `comics` / `stories` |
| `EnsureTeacherLibraryAccess` | After approval check, call `isContentTypeEnabledForUser` |
| Teacher nav (optional) | Hide library sections for disabled modules |

**Deliverable:** Teacher in a school with `games` off cannot open game library URLs (403).

---

### Phase 5 — CMS / Super Admin (light touch)

- **CMS editors** keep creating all content (platform catalog).  
- **Super Admin** global toggles on dashboard + `/admin/modules` — no code change beyond 15 rows.  
- Optional later: show badge “module disabled globally” on content index.

---

### Phase 6 — Age profiles (align, don’t merge)

- Keep `age_profiles.content_access_rules.modules` as **child** rules.  
- Document map in `config/modules.php` (org module must be on **and** age module must allow).  
- Mobile: `enabled_keys` from org **then** filter UI by age profile from `GET /age-profiles`.

---

### Suggested module keys (implement as)

| Activity # | Module key |
|------------|------------|
| 1 Story | `stories` *(alias `comics` in API during transition if needed)* |
| 2 Song | `songs` |
| 3 Flashcard | `flashcards` |
| 4 Puzzle | `puzzles` |
| 5 Drawing | `drawings` |
| 6 Colouring | `colouring` |
| 7 Language | `language_activities` |
| 8 Game | `games` |
| 9 Maze | `mazes` |
| 10 Spot difference | `spot_difference` |
| 11 Word search | `word_searches` |
| 12 Culture | `culture_activities` |
| — | `offline_bundles`, `theme_engine`, `kiosk_mode` |

---

### Order of work (practical)

```
Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5 → Phase 6
   ↑         ↑          ↑
 seed     mobile     org + teacher
```

Estimate: Phase 1–2 = foundation most apps need; Phase 3–4 = full B2B parity; Phase 6 when Expo consumes both APIs.

---

## See also

- [modules.md](./modules.md) — how resolution and API work today  
- [system-workflows-reference.md](./system-workflows-reference.md) — spec → routes map  
- [super-admin-module.md](./super-admin-module.md) — God Mode status
