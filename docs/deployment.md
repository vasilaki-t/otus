# Deployment (HW-21 — Envoy & развёртывание)

This document describes the imitation Production environment, how to deploy the
application with a **single command**, and how the test gate prevents
low-quality code from ever reaching production.

## TL;DR — one-command deploy

```bash
vendor/bin/envoy run deploy
# or the convenience wrapper:
./deploy.sh
```

That single command runs the whole pipeline: quality/test gate → pull → install
prod deps → migrate → optimize → restart queues → atomic switch.

---

## 1. Imitation Production environment ("по подобию Dev")

Dev runs on Laravel Sail (`compose.yaml`): `laravel.test` + PostgreSQL + Redis,
plus dev-only services (Selenium, Mailpit, Vite).

Production is mirrored in **`compose.prod.yaml`** — the same backing services
(PostgreSQL 17 + Redis) but:

- dev-only services removed (no Selenium / Mailpit / Vite),
- runs in production mode (`APP_ENV=production`, `APP_DEBUG=false`),
- built from **`Dockerfile.prod`** (multi-stage: `composer install --no-dev`
  + `npm run build` for assets + slim `php-fpm` runtime with opcache tuned),
- served by Nginx (`docker/prod/nginx.conf`) in front of php-fpm,
- healthchecks on every service.

### Bring the prod imitation up

```bash
cp .env.production.example .env.production
# edit .env.production: set APP_KEY, DB_PASSWORD, etc.
php artisan key:generate --env=production   # or set APP_KEY manually

docker compose -f compose.prod.yaml --env-file .env.production up -d --build
docker compose -f compose.prod.yaml ps        # check health
docker compose -f compose.prod.yaml down       # tear down
```

The app is then reachable on `http://localhost:${APP_PORT:-8080}`.

> Note: Docker may not be available in every environment. The config is provided
> as a working, production-like definition mirroring Dev; it does not need to be
> running for the Envoy deploy flow to work.

---

## 2. Automatic deploy in one command (Laravel Envoy)

The deploy is defined in **`Envoy.blade.php`** as the story `deploy`.
Configuration is taken from environment variables (no real host hard-coded):

| Variable        | Default                              | Meaning                     |
|-----------------|--------------------------------------|-----------------------------|
| `DEPLOY_HOST`   | `deploy@your-prod-host`              | SSH target                  |
| `DEPLOY_REPO`   | `git@github.com:your-org/otus.git`   | repository the prod host pulls |
| `DEPLOY_BRANCH` | `main`                               | branch or tag to release    |
| `DEPLOY_PATH`   | `/var/www/otus`                      | deploy root on prod         |

```bash
DEPLOY_HOST=deploy@1.2.3.4 DEPLOY_BRANCH=main vendor/bin/envoy run deploy
```

### Pipeline (story `deploy`)

1. **`test-gate`** *(runs locally / on CI)* — `pint --test`, `phpstan`,
   `php artisan test`. **Aborts the whole deploy on any failure.**
2. **`pull`** — fetch the branch/tag into a fresh `releases/<timestamp>` dir.
3. **`dependencies`** — `composer install --no-dev --prefer-dist
   --optimize-autoloader`.
4. **`shared-links`** — symlink shared `.env` and `storage/` into the release.
5. **`migrate`** — `php artisan migrate --force`.
6. **`optimize`** — `php artisan optimize` + config/route/view/event cache.
7. **`queue-restart`** — `php artisan queue:restart`.
8. **`activate`** — atomic `current -> releases/<timestamp>` symlink switch,
   prune old releases (keep last 5).

### Dry run

```bash
vendor/bin/envoy run deploy --pretend
```

prints every command without executing or connecting via SSH.

---

## 3. How bad code is kept off production

Two independent gates:

1. **CI quality gate** — `.github/workflows/ci.yml` runs `pint --test`,
   `phpstan` and `php artisan test` on every push and pull request (PHP 8.3 and
   8.4, with PostgreSQL + Redis service containers). A failing check turns the
   PR red and **blocks the merge** into the production branch (`main`).
2. **Envoy test gate** — the first task of the deploy story re-runs the same
   three checks. If any fails, Envoy exits non-zero and the story stops *before*
   anything touches the prod host. So even a manual `envoy run deploy` cannot
   push failing code.

Optionally, **`.github/workflows/deploy.yml`** runs the Envoy deploy
automatically after CI succeeds on `main` (or on a `v*` tag), via SSH secrets —
fully automated, test-gated delivery.

---

## 4. Quick reference

```bash
vendor/bin/envoy run deploy            # one-command deploy
vendor/bin/envoy run deploy --pretend  # dry run
./deploy.sh                            # wrapper
vendor/bin/pint --test                 # style gate (local)
vendor/bin/phpstan analyse             # static analysis gate (local)
php artisan test                       # test gate (local)
```
