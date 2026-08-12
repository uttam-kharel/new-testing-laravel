# 🌐 Shubham International Hospital

Laravel 13 + Tailwind CSS (Vite) landing site, hosted free on **Vercel** with
the community **`vercel-php` runtime** (serverless Functions — no Docker, no
Caddy, no credit card).

**Production:** https://shubham-international-hospital.vercel.app

---

## The stack (why this approach)

This repo follows the same free stack as
[`uttam-kharel/laravellivewire-sih`](https://github.com/uttam-kharel/laravellivewire-sih):
a tiny PHP front controller (`api/index.php`) that runs Laravel's normal
`public/index.php` on a Vercel Function.

| Piece                | What it does                                                        |
| -------------------- | ------------------------------------------------------------------- |
| `vercel.json`        | Pins the `vercel-php@0.7.4` runtime; routes `/build/*` to static assets, everything else to the function |
| `api/index.php`      | Fixes `SCRIPT_NAME` (so routes work), moves caches to `/tmp`, sessions→cookie, cache→array, logs→stderr |
| `.vercelignore`      | Keeps `vendor`, `node_modules`, `.env`, tests, and storage out of uploads (Vercel reinstalls Composer deps at build) |
| `bootstrap/app.php`  | Trusts forwarded proxy headers so asset/route URLs stay `https` behind Vercel's edge |

Why not the Docker/FrankenPHP container route? It's Vercel's official path for
PHP, but it needs a `Dockerfile` + `Caddyfile` per project and slower container
builds. The Functions runtime is simpler to clone into every future project,
ships faster, and sits on the standard free tier.

## Local development

Requires PHP 8.3+ and Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run dev        # terminal 1
php artisan serve  # terminal 2
```

Smoke-test the exact Vercel entrypoint locally (same wrapper the runtime uses):

```bash
php -S 127.0.0.1:8123 api/index.php
curl -H "X-Forwarded-Proto: https" http://127.0.0.1:8123/
```

## CI/CD (100% free)

| Workflow                     | When                          | What it does                                                        |
| ---------------------------- | ----------------------------- | ------------------------------------------------------------------- |
| `ci.yml`                     | every push & PR               | PHP 8.3 tests, Pint style check, Vite production build              |
| `deploy-vercel.yml`          | PRs / push to `production`    | Preview deploy + PR URL comment; production deploy on `production`  |

Both run on GitHub Actions free minutes. The deploy workflow ships with **no
secrets**, so it skips cleanly until you add them — activate it with three
GitHub secrets (Settings → Secrets and variables → Actions):

```
VERCEL_TOKEN      – Vercel → Settings → Tokens → Create
VERCEL_ORG_ID     – from .vercel/project.json → "orgId"   (team_...)
VERCEL_PROJECT_ID – from .vercel/project.json → "projectId" (prj_...)
```

## Branch flow

- **`main`** — development. CI runs on every push/PR, but nothing deploys.
- **`production`** — deployable. Push to it (or merge `main` into it, or open
  a PR against it) and Vercel deploys to production; PRs against it get
  preview URLs.

```bash
git checkout -b production && git push -u origin production   # one-time setup
git checkout main && git push origin main:production          # ship on every release
```

## Deploying (first time / new Vercel account)

```bash
npx vercel login          # interactive — pick the account
npx vercel link --yes     # creates/links the project, writes .vercel/project.json
npx vercel env add APP_KEY production       # required — Laravel boot fails without it
npx vercel env add APP_ENV production       # production
npx vercel env add APP_DEBUG production     # false
npx vercel env add APP_URL production       # https://<your-project>.vercel.app
npx vercel deploy --prod
```

> **APP_KEY is mandatory.** Generate once with
> `base64:$(php -r 'echo base64_encode(random_bytes(32));')` and keep it stable
> so encrypted cookies/sessions survive redeploys. Re-add the same vars for
> `preview` if you want preview deploys fully working.

## Production notes

- **Database:** SQLite by default, but Vercel Functions have a read-only
  filesystem and the app runs without a DB (sessions are cookies). When the
  site needs real data, switch to a free Postgres like Neon and set
  `DB_CONNECTION=pgsql` + `DATABASE_URL` (the deploy workflow then runs
  `php artisan migrate --force` automatically).
- **Uploads:** use [Vercel Blob](https://vercel.com/docs/storage/vercel-blob)
  (free tier) — the filesystem is ephemeral.
- **Logs:** visible under Vercel → Project → Logs (stderr).
- **Env vars** set in the Vercel dashboard always win over the `api/index.php`
  defaults.

## Copying this to a future project

1. `git clone` this repo (or copy `api/index.php`, `vercel.json`, `.vercelignore`, `.github/workflows/deploy-vercel.yml`).
2. `vercel link --yes`, add the env vars above, `vercel deploy --prod`.
3. Add the three GitHub secrets to enable auto-deploys.
