# Production: Images & Files Not Showing (Coolify / VPS)

This app serves uploads from **`storage/app/public`** via the **`public/storage` symlink** (URLs like `/storage/comics/...`). If images work locally but not on production, use this checklist.

---

## How this app stores files

| Item | Location |
|------|----------|
| Uploads | `storage/app/public/...` (Laravel `public` disk) |
| Web access | `public/storage` → symlink to `storage/app/public` |
| URLs in views | `asset('storage/'.$path)` or `Storage::disk('public')->url(...)` |
| Default disk | `FILESYSTEM_DISK=local` — media still uses the **`public`** disk explicitly |

---

## Most likely causes (Coolify)

### 1. No persistent volume for `storage` (most common)

Uploads live in `/var/www/html/storage` inside the container. **Every redeploy wipes that folder** unless Coolify mounts a persistent volume.

`docker-compose.yml` in this repo already defines:

```yaml
volumes:
  - app_storage:/var/www/html/storage
```

If Coolify deploys **only the Dockerfile** (not compose), that volume is often **missing**.

**Fix in Coolify**

1. Open your application → **Persistent Storage**
2. Add a mount:

   | Mount path | Volume |
   |------------|--------|
   | `/var/www/html/storage` | New persistent volume |

3. Redeploy
4. Re-upload files **or** copy local `storage/app/public` to the server (see below)

---

### 2. Storage symlink missing or wrong

The app expects:

```
public/storage → storage/app/public
```

`docker/entrypoint.sh` runs `php artisan storage:link` on startup. If `public/storage` already exists as a **directory** (not a symlink), the link can fail silently and `/storage/...` returns 404.

**Check (Coolify terminal / SSH into container)**

```bash
ls -la /var/www/html/public/storage
# Expected: storage -> /var/www/html/storage/app/public
```

**Fix**

```bash
cd /var/www/html
php artisan storage:link --force
```

---

### 3. `APP_URL` wrong in production `.env`

Set in Coolify environment variables:

```env
APP_URL=https://your-actual-domain.com
```

`config/filesystems.php` uses `APP_URL` for `Storage::url()`. Many blades use `asset('storage/...')` (relative), which usually still works, but a wrong `APP_URL` can break some generated URLs.

**After changing env**

```bash
php artisan config:clear
php artisan config:cache
```

---

### 4. Database migrated, files never copied

Paths in MySQL (e.g. `comics/panels/xyz.jpg`) only work if the **files exist** under `storage/app/public/` on the VPS.

Importing the DB from local/staging **without** copying `storage/app/public` → broken images everywhere.

**Fix — copy uploads from local machine**

```bash
# Example: rsync from your dev machine
rsync -avz ./storage/app/public/ user@your-vps:/path/to/persistent/storage/app/public/
```

Or re-upload content through the admin/CMS after deploy.

---

### 5. Permissions

PHP/nginx runs as `www-data`. The storage directory (especially on a new volume) must be writable.

```bash
chown -R www-data:www-data /var/www/html/storage
chmod -R ug+rwx /var/www/html/storage
```

---

## Quick diagnosis

1. Open a broken image URL directly in the browser, e.g.  
   `https://yourdomain.com/storage/comics/some-file.jpg`

2. Interpret the result:

   | HTTP result | Likely cause |
   |-------------|----------------|
   | **404** | Symlink missing, file missing, or wrong path |
   | **403** | Permissions |
   | **200** but UI still broken | Wrong URL in HTML, mixed content, or cached config |

3. Inside the container:

   ```bash
   ls -la storage/app/public/comics    # Do files exist?
   ls -la public/storage               # Is it a symlink?
   ```

---

## Coolify deployment checklist

- [ ] Persistent volume: `/var/www/html/storage`
- [ ] `APP_URL=https://your-production-domain` (no trailing path)
- [ ] `APP_KEY` set
- [ ] `APP_DEBUG=false` in production
- [ ] After deploy: `php artisan storage:link --force`
- [ ] Copy or sync `storage/app/public` from source environment, or re-upload in admin
- [ ] Test: `https://yourdomain.com/storage/...` returns the file (200)

---

## Environment variables (reference)

From `.env.example` — relevant to files:

```env
APP_URL=https://yourdomain.com
FILESYSTEM_DISK=local
```

S3 is optional (`AWS_*`); this project uses the local `public` disk unless you change upload code and config.

---

## Docker / entrypoint notes

- **Dockerfile** + `docker/entrypoint.sh` run migrations, seed, `storage:link`, and cache on every container start.
- If **`db:seed` fails**, the entrypoint exits before later steps (including `storage:link`). If the app is up but images fail, seeding likely succeeded; still check deploy logs.
- **Compose** users: ensure the `app_storage` volume is attached (see `docker-compose.yml`).

---

## Optional improvements (codebase)

Consider for a future deploy hardening pass:

- `php artisan storage:link --force` in entrypoint (instead of plain `storage:link`)
- `chown`/`chmod` on `storage` after volume mount
- Make `db:seed` non-fatal on production redeploys (seed only on first run)

---

## Related paths in repo

| File | Purpose |
|------|---------|
| `config/filesystems.php` | `public` disk + symlink config |
| `docker/entrypoint.sh` | Runs `storage:link` on boot |
| `docker-compose.yml` | `app_storage` volume example |
| `Dockerfile` | Production image |
| `docker/nginx.conf` | Serves `public/` (including `/storage` via symlink) |
