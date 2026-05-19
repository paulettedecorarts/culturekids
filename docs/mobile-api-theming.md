# Mobile API — Organisation Theming

> **Modules & content gating:** See [modules.md — Expo / React Native](./modules.md#expo--react-native--module-and-content-gating). Summary: gate UI with `GET /api/v1/organisation/modules` **and** `content_access_rules.effective_modules` from `GET /api/v1/age-profiles` — not age-band `modules` alone.

Guide for Expo / React Native developers integrating white-label branding from the Culture Kids backend.

When the organisation has disabled the **theme_engine** module, the API still returns **200** with the platform default palette and `"theme_engine_enabled": false`. Hide custom branding UI in the app when that flag is false.

---

## Overview

Schools and organisations can customise the app look (colours, typography, logo) from the **Super Admin / Org Admin** panel. The mobile app loads that configuration at runtime via a single API call.

You do **not** hardcode brand colours per school in the app. Fetch once after login (and refresh when needed), then map tokens to your UI theme (e.g. React Navigation, Tamagui, NativeWind, or a custom `ThemeProvider`).

---

## Endpoint

| | |
|---|---|
| **Method** | `GET` |
| **Path** | `/api/v1/organisation/theme` |
| **Auth** | Required — Laravel Sanctum bearer token |
| **Base URL** | Your API host, e.g. `https://api.culturekids.app` or `http://localhost:8000` |

### Full URL example

```
GET https://your-api-host/api/v1/organisation/theme
Authorization: Bearer {sanctum_token}
Accept: application/json
```

---

## When to call

| Moment | Action |
|--------|--------|
| **App cold start** (user already logged in) | Fetch theme after restoring token; show splash until theme is applied or fallback is ready |
| **After login / register** | Fetch immediately after storing token (login response includes `organization_id`) |
| **After logout → login as different user** | Clear cached theme; fetch again |
| **Optional refresh** | Re-fetch on app foreground if cache is older than 24h, or when user opens Settings → “Refresh branding” |

Theme is tied to the **authenticated user’s `organisation_id`**, not to a child profile. Parents (B2C, no org) receive platform defaults.

---

## Authentication

Same as other mobile endpoints (`/api/v1/age-profiles`, `/api/v1/comics`, etc.):

1. `POST /api/v1/auth/login` with `email` + `password`
2. Store `token` from the response
3. Send header on theme request:

```http
Authorization: Bearer 1|abcdef...
```

### Login response (relevant fields)

```json
{
  "message": "Login successful",
  "user": {
    "id": 42,
    "name": "Jane Parent",
    "email": "jane@school.org",
    "roles": ["teacher"],
    "organization_id": 7
  },
  "token": "1|..."
}
```

- `organization_id: null` → expect `source: "platform_default"` (or `platform_theme` if a global theme exists)
- `organization_id: 7` → expect organisation branding for school `7`

---

## Success response

**HTTP 200**

```json
{
  "theme": {
    "source": "organisation_theme",
    "organisation_id": 7,
    "theme_id": 12,
    "name": "Sunrise Primary Brand",
    "slug": "sunrise_primary",
    "logo_url": "https://your-cdn.example/storage/logos/sunrise.png",
    "colors": {
      "primary": "#2E4D8A",
      "secondary": "#4A72C4",
      "accent": "#D4A017",
      "success": "#4A7C59",
      "warning": "#F2A84E",
      "danger": "#9A3218",
      "background": "#FAF6F0",
      "surface": "#FFFFFF",
      "text_primary": "#1A1208",
      "text_secondary": "#6B5544",
      "text_muted": "#9C8875"
    },
    "typography": null,
    "spacing": null,
    "borders": null,
    "metadata": {}
  }
}
```

### Field reference

| Field | Type | Description |
|-------|------|-------------|
| `source` | string | How the theme was resolved (see table below) |
| `organisation_id` | number \| null | School/org ID, or `null` for B2C parents |
| `theme_id` | number \| null | ID of the `themes` row used, or `null` for built-in default |
| `name` | string | Display name of the theme |
| `slug` | string \| null | Stable identifier (e.g. for analytics); may be `null` on platform default |
| `logo_url` | string \| null | Organisation logo URL when configured |
| `colors` | object | **Always complete** — every key below is present (hex strings `#RRGGBB`) |
| `typography` | object \| null | Optional font tokens (reserved for future use) |
| `spacing` | object \| null | Optional spacing scale (reserved) |
| `borders` | object \| null | Optional border radius tokens (reserved) |
| `metadata` | object | Extra key/value pairs from admin (app-specific flags) |

### `source` values

| Value | Meaning |
|-------|---------|
| `platform_default` | No theme record found; using built-in Culture Kids palette |
| `platform_theme` | Global theme (`org_id` null) marked default in admin |
| `organisation_theme` | Organisation’s default active theme from **Themes** admin |
| `organisation_override` | Base theme **plus** extra JSON stored on the organisation record (admin override) |

Use `source` for debugging or analytics only — **always apply `colors`** regardless of source.

### Color tokens (always provided)

| Token | Typical use |
|-------|-------------|
| `primary` | Primary buttons, active tab, key CTAs |
| `secondary` | Secondary actions, highlights |
| `accent` | Badges, stars, playful accents |
| `success` | Correct answers, completion states |
| `warning` | Gentle warnings, “almost there” |
| `danger` | Errors, destructive actions |
| `background` | Screen background |
| `surface` | Cards, modals, sheets |
| `text_primary` | Headings, body text |
| `text_secondary` | Subtitles, labels |
| `text_muted` | Hints, placeholders, disabled text |

The API **merges** organisation-specific colours onto a full default palette, so missing keys in admin never reach the app as `null`.

---

## How the backend resolves theme (for debugging)

Priority order:

1. User’s `organisation_id` → find active theme with `is_default = true` for that org  
2. If none marked default → latest active theme for that org  
3. If user has no org → global default theme (`org_id` null, `is_default = true`)  
4. If no theme row → `platform_default` with hard-coded Culture Kids colours  
5. If organisation has extra `theme` JSON in settings → deep-merge on top; `source` becomes `organisation_override`

---

## Error responses

| HTTP | Meaning | App behaviour |
|------|---------|---------------|
| **401** | Missing or invalid token | Redirect to login |
| **403** | Rare — user blocked | Show error, offer logout |
| **5xx** | Server error | Use last cached theme; if none, use local fallback palette (see below) |

There is no **404** for this endpoint when the route is correct (`/api/v1/organisation/theme`).

---

## Recommended client implementation

### 1. TypeScript types

```typescript
export type ThemeSource =
  | 'platform_default'
  | 'platform_theme'
  | 'organisation_theme'
  | 'organisation_override';

export interface ThemeColors {
  primary: string;
  secondary: string;
  accent: string;
  success: string;
  warning: string;
  danger: string;
  background: string;
  surface: string;
  text_primary: string;
  text_secondary: string;
  text_muted: string;
}

export interface OrganisationTheme {
  source: ThemeSource;
  organisation_id: number | null;
  theme_id: number | null;
  name: string;
  slug: string | null;
  logo_url: string | null;
  colors: ThemeColors;
  typography: Record<string, unknown> | null;
  spacing: Record<string, unknown> | null;
  borders: Record<string, unknown> | null;
  metadata: Record<string, unknown>;
}

export interface ThemeApiResponse {
  theme: OrganisationTheme;
}
```

### 2. Fetch helper (Expo / fetch)

```typescript
const API_BASE = process.env.EXPO_PUBLIC_API_URL; // e.g. https://api.culturekids.app

export async function fetchOrganisationTheme(
  token: string,
): Promise<OrganisationTheme> {
  const res = await fetch(`${API_BASE}/api/v1/organisation/theme`, {
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: 'application/json',
    },
  });

  if (res.status === 401) {
    throw new Error('UNAUTHORIZED');
  }
  if (!res.ok) {
    throw new Error(`THEME_FETCH_FAILED:${res.status}`);
  }

  const data: ThemeApiResponse = await res.json();
  return data.theme;
}
```

### 3. Apply to React Native (example)

Map API snake_case tokens to your design system:

```typescript
import { OrganisationTheme } from './types';

export function themeToNavigation(theme: OrganisationTheme) {
  const c = theme.colors;
  return {
    dark: false,
    colors: {
      primary: c.primary,
      background: c.background,
      card: c.surface,
      text: c.text_primary,
      border: c.text_muted,
      notification: c.accent,
    },
  };
}

// Usage after login:
// const theme = await fetchOrganisationTheme(token);
// await AsyncStorage.setItem('ck_theme', JSON.stringify(theme));
// setNavigationTheme(themeToNavigation(theme));
```

### 4. Local fallback (offline / API failure)

If the request fails and there is no cache, use the same defaults as the server (`platform_default`):

```typescript
export const FALLBACK_THEME: OrganisationTheme = {
  source: 'platform_default',
  organisation_id: null,
  theme_id: null,
  name: 'Culture Kids Default',
  slug: null,
  logo_url: null,
  colors: {
    primary: '#C44B2B',
    secondary: '#E8872A',
    accent: '#D4A017',
    success: '#4A7C59',
    warning: '#F2A84E',
    danger: '#9A3218',
    background: '#FAF6F0',
    surface: '#FFFFFF',
    text_primary: '#1A1208',
    text_secondary: '#6B5544',
    text_muted: '#9C8875',
  },
  typography: null,
  spacing: null,
  borders: null,
  metadata: {},
};
```

### 5. Caching

Suggested cache key: `ck_theme_v1_{organisation_id ?? 'b2c'}_{theme_id ?? 'default'}`

Store the full `theme` object in AsyncStorage (or MMKV). Invalidate when:

- `user.organization_id` from login differs from cached `organisation_id`
- Manual refresh
- Optional: compare `theme_id` after each successful fetch

---

## Startup sequence (with other config)

Align with organisation modules, age profiles, and catalog loading:

```
1. Restore Sanctum token from secure storage
2. GET /api/v1/auth/me                → confirm user + organization_id
3. GET /api/v1/organisation/modules   → school licence (which product areas exist)
4. GET /api/v1/organisation/theme     → apply branding
5. GET /api/v1/age-profiles           → child UI rules (use effective_modules per band)
6. Navigate to home / child picker
```

Parallelising steps 3–5 is fine once the token is valid.

Full gating rules (org + age, `stories` vs `/comics`, caching): [modules.md — Expo / React Native](./modules.md#expo--react-native--module-and-content-gating).

---

## B2C vs B2B behaviour

| User type | `organization_id` | Typical `source` | Branding |
|-----------|-------------------|------------------|----------|
| Parent (self-registered) | `null` | `platform_default` | Culture Kids default |
| Teacher / org staff | set | `organisation_theme` | School colours + logo |
| Parent linked to school | set | `organisation_theme` | School colours + logo |

Child profiles do **not** change the theme endpoint; theme is per **logged-in account**.

---

## Logo

- Field: `theme.logo_url`
- May be `null` — hide school logo or show app default asset
- Treat as absolute URL; use your image component’s caching (expo-image recommended)
- CORS / HTTPS: logos are served from the same app domain or configured storage URL

---

## Future fields

`typography`, `spacing`, and `borders` are optional and often `null` today. When admin starts populating them, merge into your theme the same way as `colors` without an app update (forward-compatible).

Example future shape:

```json
"typography": {
  "font_family_heading": "Fredoka",
  "font_family_body": "Nunito",
  "scale": "large"
}
```

Ignore unknown keys; do not crash on extra properties.

---

## Testing

### cURL (after login)

```bash
TOKEN="your_sanctum_token"

curl -s -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  https://localhost:8000/api/v1/organisation/theme | jq
```

### PHPUnit (backend)

```bash
php artisan test --filter=OrganisationThemeApiTest
```

---

## Related documentation

- **Module & content gating (Expo):** [modules.md](./modules.md#expo--react-native--module-and-content-gating)
- Internal gap analysis: [super-admin-module.md](./super-admin-module.md) (Phase 2)
- Age profiles API: same auth pattern — `GET /api/v1/age-profiles`
- Auth: `POST /api/v1/auth/login`, `GET /api/v1/auth/me`

---

## Changelog

| Date | Change |
|------|--------|
| 2026-05-19 | Initial theme endpoint: `GET /api/v1/organisation/theme` |
