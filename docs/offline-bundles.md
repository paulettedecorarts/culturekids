# Offline bundles (`.ckb`)

Pre-built ZIP archives for low-connectivity use in the Expo parent/child app. Each published item can have one `.ckb` file containing `manifest.json` plus copied assets from `storage/app/public`.

## Content types (12)

All organisation review content types are supported:

| `content_type` | Source model |
|----------------|--------------|
| `story` | `Comic` (published) |
| `song` | `Song` |
| `flashcard` | `Activity` (`type = flashcard`, published) |
| `puzzle` | `Activity` (`type = puzzle`, published) |
| `drawing` | `Drawing` (`drawing_type = drawing`) |
| `colouring` | `Drawing` (`drawing_type = coloring`) |
| `language` | `LanguageActivity` |
| `game` | `Game` |
| `maze` | `Maze` |
| `spot_difference` | `SpotDifference` |
| `word_search` | `WordSearch` |
| `culture` | `CultureActivity` |

Module gating still applies: `offline_bundles` plus the per-type module (e.g. `stories`, `puzzles`) must be enabled for the organisation.

## Manifest schema

- **v2 (all types):** `culturekids.bundle.v2` — `content_type`, `content_id`, `title`, `tribe_id`, `asset_map`, `data` (type-specific payload).
- **Stories only:** nested `legacy` block with `culturekids.bundle.v1` shape for older mobile readers.

## Build pipeline

1. **Queue:** `BuildOfflineBundle` job on `media-processing`.
2. **Service:** `OfflineBundleBuilder::build($contentType, $contentId)`.
3. **Storage:** `offline_content_bundles` table + file at `public/bundles/{org|global}/{type}-{id}.ckb`.
4. **Stories:** also updates `comics.bundle_path` / `bundle_hash` for backward compatibility.

**Triggers:**

- Org approval when content moves to published (`OrganisationContentReviewService` → `OfflineBundlePublisher::queue`).
- Manual **Rebuild** on `/cms/editor/offline-bundles`.

**Requirements:** PHP `ZipArchive` extension; queue worker must run `media-processing`.

## Mobile API

| Method | Path | Purpose |
|--------|------|---------|
| GET | `/api/v1/offline/tribes/{tribeId}/bundle` | Tribe manifest (metadata; includes `bundle_ready` / `bundle_download_url` per item where built) |
| GET | `/api/v1/offline/tribes/{tribeId}/assets` | Per-asset URLs (comics + songs; optional fallback when no `.ckb`) |
| GET | `/api/v1/offline/comics/{comicId}/download` | Legacy story `.ckb` download |
| GET | `/api/v1/offline/content/{contentType}/{contentId}/download` | **Any** of the 12 types |

## CMS

**Content Studio → Offline Bundles** lists all published items with live status (Ready, Queued…, Building…, Failed, Not built), summary counts, **Build missing** / **Build all (filtered)** bulk actions, and per-row **Rebuild**. The page auto-refreshes every 3s while builds are in progress (requires queue worker — see `start.sh` / Docker supervisord).

## See also

- [modules.md](./modules.md) — `offline_bundles` module flag
- [production-storage-coolify.md](./production-storage-coolify.md) — persistent `storage` on deploy
