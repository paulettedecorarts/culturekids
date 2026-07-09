# Local development setup (Windows)

Laravel runs on the host (`php artisan serve`). MySQL and Redis run in Docker using credentials from `docker-compose.yml`.

## 1. Prerequisites

- PHP 8.3+ (CLI on `PATH`, e.g. `C:\php`)
- Composer
- Node.js 22+
- Docker Desktop

PHP extensions needed for day-to-day dev: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`.

`ext-imagick` is only required for PDF comic panel extraction. On Windows it is often missing — install dependencies with the flag below until you add Imagick.

## 2. Start database and Redis

```powershell
docker compose -f docker-compose.yml -f docker-compose.dev.yml up mysql redis -d
```

This exposes:

| Service | Host connection |
|---------|-----------------|
| MySQL   | `127.0.0.1:3307` |
| Redis   | `127.0.0.1:6380` |

Credentials (from `docker-compose.yml`):

- Database: `paulette`
- User: `paulette`
- Password: `PauletteKwatagig2024`

If Redis was started without the dev override, recreate containers:

```powershell
docker compose -f docker-compose.yml -f docker-compose.dev.yml up mysql redis -d --force-recreate
```

## 3. Configure `.env`

Copy `.env.example` to `.env` if needed, then ensure these values for **host** development:

```env
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=paulette
DB_USERNAME=paulette
DB_PASSWORD=PauletteKwatagig2024

REDIS_HOST=127.0.0.1
REDIS_PORT=6380
```

Do **not** use `DB_HOST=mysql` or `REDIS_HOST=redis` when running Laravel outside Docker — those hostnames only resolve inside the compose network.

Generate an app key if missing:

```powershell
php artisan key:generate
```

## 4. Install dependencies

```powershell
composer install --ignore-platform-req=ext-imagick
npm install
```

## 5. Database

```powershell
php scripts/prepare_heritage_seed.php
php artisan config:clear
php artisan migrate
php artisan db:seed
```

Seeded super admin: `admin@culturekids.app` / `password`

## 6. Run the app

**API only:**

```powershell
composer serve
# or
serve.bat
```

**Full dev stack** (server + queue + logs + Vite):

```powershell
composer dev
```

Open http://127.0.0.1:8000

Health check: http://127.0.0.1:8000/health

## Troubleshooting

### `getaddrinfo for redis failed [tcp://redis:6379]`

`.env` still points at Docker internal hostnames, or config is cached:

```powershell
php artisan config:clear
```

Set `REDIS_HOST=127.0.0.1` and `REDIS_PORT=6380`.

### `composer install` fails on `ext-imagick`

Use `--ignore-platform-req=ext-imagick`. PDF upload/processing features need Imagick + Ghostscript installed separately on Windows.

### MySQL connection refused

Confirm Docker is running and MySQL is healthy:

```powershell
docker compose -f docker-compose.yml -f docker-compose.dev.yml ps
```

## Stop infrastructure

```powershell
docker compose -f docker-compose.yml -f docker-compose.dev.yml down
```
