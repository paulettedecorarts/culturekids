# Translations

Culture Kids has **three related translation systems**. They are intentionally separate: story panel taps, language-activity word lists, and the languages registry.

## 1. Story panel vocabulary (`panel_vocab_tags`)

**Purpose:** Tap-to-translate hotspots on comic/story panels (tribe language word + English gloss + phonetic).

| Layer | Detail |
|-------|--------|
| Table | `panel_vocab_tags` → model `PanelVocabTag` |
| Fields | `word`, `translation`, `phonetic`, `x_position`, `y_position`, `width`, `height` |
| Admin | Super Admin → **Translations**, or **Story packs → Panel editor** |
| CMS editor | `/cms/editor/translations` (same `TranslationsManager` component) |

### Mobile API

- **Online:** `GET /api/v1/comics/{id}` — each panel includes `vocab_tags[]` (same shape as offline).
- **Offline:** tribe/comic bundles include `vocab_tags` on each panel.

Serializer: `App\Support\PanelVocabTagSerializer`.

> The legacy JSON column `comic_panels.vocab_tags` is unused; normalized rows are the source of truth.

---

## 2. Language activities (`language_activities` + `language_activity_words`)

**Purpose:** Structured language lessons (word trace, audio match, proverb jumble, etc.).

| Layer | Detail |
|-------|--------|
| Tables | `language_activities`, `language_activity_words` |
| Word fields | `word`, `translation`, `phonetic`, `emoji`, audio/image paths |
| Activity fields | `sentence_translation` for proverb/sentence builder types |
| Module key | `language_activities` (activity type `vocab_pack`) |
| Admin | CMS → **Language activities** |

### Mobile API

`GET /api/v1/activities/{id}` when `type === vocab_pack` includes:

```json
{
  "language_activity": {
    "id": 1,
    "activity_type": "word_trace",
    "language_code": "lug-UG",
    "words": [{ "word": "PIJ", "translation": "Water", ... }]
  }
}
```

Serializer: `App\Support\LanguageActivityApiSerializer`.

Legacy `activities` rows are mirrored from `LanguageActivity` via `metadata.legacy_language_activity_id`.

---

## 3. Languages registry (`languages`)

**Purpose:** Platform language list for the app (name, code, flag, coverage %, audio pack flag).

| Layer | Detail |
|-------|--------|
| API | `GET /api/v1/languages` |
| Coverage | **Auto-calculated** from language-activity words (+ sentence slots for proverb/sentence types) |
| Service | `App\Services\TranslationCoverageService` |
| Status | Derived from coverage: ≥80% `verified`, ≥40% `partial`, else `pending` |

Panel vocab tags do **not** affect `languages.translation_coverage` (different concern).

Super Admin → **Languages** shows computed coverage (read-only in the form).

---

## Quick comparison

| Feature | Panel vocab | Language activities | Languages registry |
|---------|-------------|---------------------|-------------------|
| Scoped to | Comic panel | Tribe + `language_code` | Global code (`lug-UG`, …) |
| Mobile field | `panels[].vocab_tags` | `language_activity` on activity show | `languages[].translation_coverage` |
| Org module | `stories` | `language_activities` | *(none)* |

See also: [module-catalog.md](./module-catalog.md), [doc/activity-samples.md](../doc/activity-samples.md) (language activity samples).
