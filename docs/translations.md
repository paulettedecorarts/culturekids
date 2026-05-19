# Translations

Culture Kids manages vocabulary and glosses through a **unified admin** backed by `content_translations`, while language activities and the languages registry keep their own tables for structured lessons and platform language metadata.

## Unified content translations (`content_translations`)

**Purpose:** Word / translation / phonetic entries tied to any of the **12 content activity types** (stories, songs, flashcards, puzzles, drawing, colouring, language activities, games, mazes, spot-the-difference, word search, culture activities).

| Layer | Detail |
|-------|--------|
| Table | `content_translations` → model `ContentTranslation` |
| Config | `config/content_translations.php` — type → model, title column, sub-items |
| Fields | `content_type`, `content_id`, `sub_item_key`, `panel_id` (stories), `word`, `translation`, `phonetic`, optional hotspot coords |
| Legacy alias | `PanelVocabTag` extends `ContentTranslation` (story scope) for panel editor compatibility |
| Catalog | `ContentTranslationCatalogService` — type/content/sub-item dropdowns, context labels |
| Persistence | `ContentTranslationPersistenceService` — syncs to native models where applicable (language words, flashcard slides, word-search JSON, culture proverb) |
| Admin | Super Admin → **Translations** (list + filters), **Add** / **Edit** split workspace: type-specific fields **left**, read-only source preview **right** |
| CMS editor | Same routes under `/cms/editor/translations` |
| Presenter | `ContentTranslationFormPresenter` — field labels per activity/sub-type, source preview payloads, “Load from source” mapping |

### Admin form layout

1. **Choose content** — activity type, content item (and sub-item if the list is empty until the workspace opens).
2. **Split workspace** (when content is selected):
   - **Left:** Translation fields aligned with that type’s CRUD editor (e.g. story hotspot coords, flashcard front/back labels, language word row, word-search hint, culture proverb).
   - **Right:** Existing source version (panel image + hotspots, flashcard front/back preview, language word row, etc.) with a **sub-item navigator** (panels, cards, words) where applicable.
3. **Load from source** — copies native field values into the translation form so mappings stay in sync with the activity editor.

### Sub-items by type (all 12)

| Activity type | Parts you can translate |
|---------------|-------------------------|
| Story | Panels (`panel:{id}`) + hotspot coords |
| Song | Full lyrics or timed segments (`segment:{id}`) |
| Flashcard | Cards (`slide:{id}`) |
| Puzzle | Content tag, description (`field:tag`, `field:description`) |
| Drawing / Colouring | Type-specific fields (hero, prompts, colour labels, scene text) |
| Language | Sentence + each vocab word (`word:{id}`) |
| Game | Each question (`question:{id}`) |
| Maze | Hero + collectibles (`field:hero_character`, `collectible:{n}`) |
| Spot the difference | Each zone label (`zone:{id}`) |
| Word search | Each grid word (`ws:{index}`) |
| Culture | Proverb + main content (`field:proverb`, `field:content`) |

Native sync writes back to the same tables/JSON the CMS editors use (`ContentTranslationSubItemResolver`).

### Mobile API (stories)

- **Online:** `GET /api/v1/comics/{id}` — each panel includes `vocab_tags[]`.
- **Offline:** tribe/comic bundles include `vocab_tags` on each panel.

Serializer: `App\Support\PanelVocabTagSerializer` (accepts `ContentTranslation` or `PanelVocabTag`).

---

## Language activities (`language_activities` + `language_activity_words`)

**Purpose:** Structured language lessons (word trace, audio match, proverb jumble, etc.). Words can be edited in **Language activities** or mirrored via **Translations** when `content_type` is `language`.

| Layer | Detail |
|-------|--------|
| Tables | `language_activities`, `language_activity_words` |
| Word fields | `word`, `translation`, `phonetic`, `emoji`, audio/image paths |
| Activity fields | `sentence_translation` for proverb/sentence builder types |
| Module key | `language_activities` (activity type `vocab_pack`) |
| Admin | CMS → **Language activities** |

### Mobile API

`GET /api/v1/activities/{id}` when `type === vocab_pack` includes `language_activity` with `words[]`.

Serializer: `App\Support\LanguageActivityApiSerializer`.

---

## Languages registry (`languages`)

**Purpose:** Platform language list (name, code, flag, coverage %, audio pack flag).

| Layer | Detail |
|-------|--------|
| API | `GET /api/v1/languages` |
| Coverage | **Auto-calculated** from language-activity words (+ sentence slots for proverb/sentence types) |
| Service | `App\Services\TranslationCoverageService` |
| Status | Derived from coverage: ≥80% `verified`, ≥40% `partial`, else `pending` |

Story panel vocab and other `content_translations` rows do **not** affect `languages.translation_coverage`.

Super Admin → **Languages** shows computed coverage (read-only in the form).

---

## Quick comparison

| Feature | Content translations | Language activities | Languages registry |
|---------|----------------------|---------------------|-------------------|
| Scoped to | Any of 12 content types | Tribe + `language_code` | Global code (`lug-UG`, …) |
| Mobile field | e.g. `panels[].vocab_tags` (stories) | `language_activity` on activity show | `languages[].translation_coverage` |
| Org module | Per type in module catalog | `language_activities` | *(none)* |

See also: [module-catalog.md](./module-catalog.md), [doc/activity-samples.md](../doc/activity-samples.md).
