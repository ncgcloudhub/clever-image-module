# Deployment Guide

This project uses a **single Dockerfile** for all three environments: local, dev VPS, and prod VPS. The container runs nginx + PHP-FPM via supervisord, serving HTTP on port 8000.

---

## Architecture

```
┌─────────────────────────────────────────────┐
│  Single Container (all environments)        │
│                                             │
│  supervisord                                │
│    ├── nginx       → listens on :8000       │
│    └── php-fpm     → listens on 127.0.0.1:9000 │
│                                             │
│  nginx proxies PHP requests to php-fpm      │
└─────────────────────────────────────────────┘
```

| Environment            | How it runs                                                  | Port                  |
| ---------------------- | ------------------------------------------------------------ | --------------------- |
| Local (docker-compose) | Single app container with nginx+PHP-FPM, volume-mounted code | 8000 (mapped to 8001) |
| VPS Dev (Coolify)      | Same image, env vars from Coolify UI                         | 8000                  |
| VPS Prod (Coolify)     | Same image, different Coolify app + env vars                 | 8000                  |

---

## Local Development

### Prerequisites

- Docker and Docker Compose installed

### Setup

1. Copy the environment template:
   ```bash
   cp .env.example .env
   ```

2. Generate an app key:
   ```bash
   docker compose run --rm app php artisan key:generate
   ```

3. Start the services:
   ```bash
   docker compose up -d --build
   ```

4. Run migrations:
   ```bash
   docker compose exec app php artisan migrate
   ```

5. Access the app at `http://localhost:8001`

### Key local `.env` values

```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001
DB_HOST=db
REDIS_HOST=redis
AISITE_OAUTH_REDIRECT_URI=http://localhost:8001/oauth/callback
```

The docker-compose build passes `COMPOSER_DEV=1` so dev dependencies are included locally.

---

## Coolify VPS Deployment (Dev & Prod)

Both dev and prod VPS environments use the same Dockerfile. The only difference is environment variables.

### Coolify Application Configuration

| Setting              | Value          |
| -------------------- | -------------- |
| **Build Pack**       | Dockerfile     |
| **Dockerfile Location** | `/Dockerfile` |
| **Port Exposes**     | `8000`         |

### Environment Variables

Use the template files as a reference for what to set in Coolify:

- **Dev VPS**: See `.env.dev.example`
- **Prod VPS**: See `.env.prod.example`

Set these in Coolify → your application → **Environment Variables**.

### Critical variables to set correctly

| Variable                    | Dev VPS                                          | Prod VPS                                        |
| --------------------------- | ------------------------------------------------ | ----------------------------------------------- |
| `APP_ENV`                   | `staging`                                        | `production`                                    |
| `APP_DEBUG`                 | `true`                                           | `false`                                         |
| `APP_URL`                   | `https://image-dev.clevercreator.ai`             | `https://image.clevercreator.ai`                |
| `DB_HOST`                   | Coolify internal hostname                        | Coolify internal hostname                       |
| `REDIS_URL`                 | Full Redis connection URL from Coolify           | Full Redis connection URL from Coolify          |
| `AISITE_OAUTH_REDIRECT_URI` | `https://image-dev.clevercreator.ai/oauth/callback` | `https://image.clevercreator.ai/oauth/callback` |
| `QUEUE_CONNECTION`          | `redis`                                          | `redis`                                         |
| `SESSION_DRIVER`            | `redis`                                          | `redis`                                         |

**Important**: OAuth redirect URI must use `https://` (Traefik redirects HTTP to HTTPS).

### Deploy

After setting the Dockerfile path, port, and env vars in Coolify, trigger a redeploy.

---

## Checklist

### Coolify Settings

- [ ] Build Pack = `Dockerfile`
- [ ] Dockerfile Location = `/Dockerfile`
- [ ] Port Exposes = `8000`
- [ ] Domain configured (e.g. `https://image-dev.clevercreator.ai`)

### Environment Variables (in Coolify)

- [ ] `APP_KEY` is set (generate with `php artisan key:generate --show`)
- [ ] `APP_URL` matches your domain with `https://`
- [ ] `APP_ENV` = `staging` (dev) or `production` (prod)
- [ ] `APP_DEBUG` = `false` for production
- [ ] `DB_HOST` = Coolify internal hostname (not `db`)
- [ ] `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` match your Coolify PostgreSQL resource
- [ ] `REDIS_URL` = full connection string from Coolify Redis resource
- [ ] `AISITE_OAUTH_REDIRECT_URI` uses `https://` and your real domain
- [ ] `MAIL_MAILER` = `log` (dev) or real SMTP config (prod)

---

## Debugging

If you get **Bad Gateway**:

1. **Check Dockerfile Location**: Must be `/Dockerfile` (not `/Dockerfile.prod`).
2. **Check Port Exposes**: Must be `8000`.
3. **Check Logs**: Coolify → Logs tab — look for PHP/Laravel errors, DB connection failures.
4. **Check DB connectivity**: Verify `DB_HOST` is the Coolify internal hostname, not `db`.
5. **Check Redis**: Verify `REDIS_URL` is correct and the Redis resource is running.

If you get **OAuth errors**:

1. Ensure `AISITE_OAUTH_REDIRECT_URI` uses `https://` (Traefik forces HTTPS).
2. Ensure the redirect URI matches exactly what's registered with the OAuth provider.
