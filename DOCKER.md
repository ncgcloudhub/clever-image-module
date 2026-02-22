# Docker Setup Guide

Everything you need to run, develop, and share this Laravel application using Docker.

---

## What's Running

| Service | Image          | Purpose              | Host Port |
|---------|----------------|----------------------|-----------|
| `app`   | php:8.2-fpm    | Laravel (PHP-FPM)    | —         |
| `nginx` | nginx:alpine   | Web server           | **8001**  |
| `db`    | postgres:15    | PostgreSQL database  | 5432      |
| `redis` | redis:7        | Cache                | 6379      |

---

## Requirements

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows / macOS)
  or Docker Engine + Docker Compose v2 (Linux)
- Git

---

## First-Time Setup

### 1. Clone the repository

```bash
git clone https://github.com/your-username/0auth-visual-tools.git
cd 0auth-visual-tools
```

### 2. Create your `.env` file

```bash
cp .env.example .env
```

Open `.env` and fill in these values:

| Variable                    | What to set                                   |
|-----------------------------|-----------------------------------------------|
| `APP_KEY`                   | Leave blank — generated in step 4             |
| `DB_PASSWORD`               | Any password (must match across restarts)     |
| `AISITE_OAUTH_BASE_URL`     | URL of the OAuth server                       |
| `AISITE_OAUTH_CLIENT_ID`    | Your OAuth client ID                          |
| `AISITE_OAUTH_CLIENT_SECRET`| Your OAuth client secret                      |

> **Important:** Keep `DB_HOST=db` and `REDIS_HOST=redis` — these are the Docker
> service names. Do **not** change them to `127.0.0.1`.

### 3. Build and start

```bash
docker compose up -d --build
```

First build takes a few minutes (installs PHP + Node dependencies, compiles assets).
Subsequent starts are instant.

### 4. Generate the app key and run migrations

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### 5. Open the app

Visit [http://localhost:8001](http://localhost:8001)

---

## Making Changes

### PHP / Blade / Routes / Config — instant

These files are mounted live from your local machine. Save → refresh. No rebuild needed.

### CSS / JavaScript

After editing files in `resources/css/` or `resources/js/`:

```bash
docker compose exec app npm run build
```

For continuous hot-reload during active frontend work:

```bash
docker compose exec app npm run dev
```

### Adding a Composer package

```bash
docker compose exec app composer require vendor/package-name
```

### Adding an npm package

```bash
docker compose exec app npm install package-name
docker compose exec app npm run build
```

---

## Everyday Commands

```bash
# Start containers in background
docker compose up -d

# Stop containers (data is preserved)
docker compose down

# View live logs
docker compose logs -f app
docker compose logs -f nginx

# Open a shell inside the app container
docker compose exec app sh

# Run any Artisan command
docker compose exec app php artisan <command>

# Database migrations
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:rollback

# Clear all caches
docker compose exec app php artisan optimize:clear
```

---

## Sharing on GitHub

### You (the owner)

1. `.env` is already excluded by `.gitignore` — your credentials stay local
2. Keep `.env.example` updated whenever you add a new environment variable
3. Commit and push as normal:

```bash
git add .
git commit -m "your message"
git push
```

### The person you're sharing with

1. Clone the repo
2. Follow [First-Time Setup](#first-time-setup) above
3. Get the OAuth credentials (`AISITE_OAUTH_*`) from you — never commit these

---

## Rebuilding After Dependency Changes

If `composer.json` or `package.json` changes (you or a collaborator added packages):

```bash
docker compose up -d --build
```

---

## Troubleshooting

### Port already in use

Edit `APP_PORT` in `.env` to a free port, then restart:

```bash
docker compose down
docker compose up -d
```

### Database connection refused

Make sure `DB_HOST=db` in your `.env` (not `127.0.0.1`).
Also confirm the db container is healthy:

```bash
docker compose ps
```

### Redis connection refused

Make sure `REDIS_HOST=redis` in your `.env` (not `127.0.0.1`).

### Storage permission error

```bash
docker compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
```

### "No application encryption key" error

```bash
docker compose exec app php artisan key:generate
```

### Complete reset (start fresh)

```bash
docker compose down -v          # removes containers AND all volumes (DB data too)
docker compose up -d --build    # rebuild everything
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```
