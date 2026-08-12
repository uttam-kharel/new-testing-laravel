# 🚀 Getting Started: Laravel → GitHub → Vercel (100% Free)

A beginner-friendly, step-by-step guide that takes you from **zero** to a live
Laravel website hosted on Vercel with automatic CI/CD — exactly how this repo
(`shubham-international-hospital`) was built.

No prior deployment experience needed. Everything here uses **free tiers only,
no credit card**:

| Service | What it's for | Cost |
| --- | --- | --- |
| GitHub | Code hosting + CI/CD (Actions) | Free (2,000 min/month on private repos, unlimited on public) |
| Vercel | Hosting the site | Free Hobby plan (includes the container runtime usage this project needs) |
| Docker (local) | Testing the exact production image | Free |

---

## 1. Install the prerequisites (one time)

| Tool | What it is | Why you need it | Install |
| --- | --- | --- | --- |
| PHP 8.3+ | The language Laravel runs on | Required to run Laravel | [php.net](https://www.php.net/downloads) or `brew install php` (Mac) |
| Composer | PHP package manager | Installs Laravel and dependencies | [getcomposer.org](https://getcomposer.org/download) |
| Node.js 20+ | JavaScript runtime | Builds Tailwind/CSS assets with Vite | [nodejs.org](https://nodejs.org) |
| Git | Version control | Track your code, push to GitHub | [git-scm.com](https://git-scm.com) |
| Docker | Container engine | Test the exact production image locally | [docker.com](https://www.docker.com/products/docker-desktop) |

Verify everything works:

```bash
php -v        # should print PHP 8.3 or higher
composer -V   # should print a version
node -v       # should print v20 or higher
git --version # should print a version
docker --version
```

> **New to the terminal?** On Mac/Linux, paste the commands into Terminal. On
> Windows, use Git Bash (installed with Git). All commands in this guide work
> in all three.

You also need two free accounts:
- **GitHub** → [github.com](https://github.com) (Sign up → *Verify email*)
- **Vercel** → [vercel.com](https://vercel.com) (click *Sign Up* → *Continue with GitHub*)

---

## 2. Create a Laravel project

Open a terminal in the folder where you keep projects, then:

```bash
composer create-project laravel/laravel my-hospital
cd my-hospital
```

This creates a fresh Laravel project (this repo is Laravel 13) with:
- `app/` — your application code
- `resources/views/` — the HTML templates (Blade files)
- `routes/` — URL definitions
- `public/` — the web-accessible folder
- `database/` — database config and migrations

**Start it locally** (the classic "it works!" moment):

```bash
# 1. Create your environment file (Laravel's settings)
cp .env.example .env

# 2. Generate a secret key (Laravel refuses to run without it)
php artisan key:generate

# 3. Set up a database — Laravel ships with SQLite, just create an empty file
touch database/database.sqlite

# 4. Run the database migrations (creates Laravel's tables)
php artisan migrate

# 5. Install the frontend packages
npm install

# 6. Start the dev server
php artisan serve
```

Open **http://127.0.0.1:8000** in your browser. You should see the default
Laravel welcome page. 🎉

> **`php artisan serve`** is just a dev server. It runs your PHP code directly
> from the folder — no Docker, no Vercel yet.

---

## 3. Add Tailwind CSS (this project uses it)

Laravel 13 ships with **Tailwind CSS v4** already wired into Vite — no config
files to create.

**Build the frontend while developing** (terminal 1):

```bash
npm run dev
```

Every time you edit a Blade template and refresh, the styles rebuild
instantly.

**Verify the production build** (run this once to confirm it compiles):

```bash
npm run build
```

This creates a `public/build/` folder with minified CSS/JS — the files that
actually ship to production.

---

## 4. Build your welcome page

The homepage is `resources/views/welcome.blade.php` and is served by the route
in `routes/web.php`:

```php
Route::get('/', function () {
    return view('welcome');
});
```

Edit `welcome.blade.php` freely — it's plain HTML plus Tailwind classes. This
repo's version is a hospital landing page with a header, hero, "What's Coming"
cards, contact section, and footer.

**Tips for a first page:**
- Keep the `<title>` tag accurate (shows in the browser tab and Google).
- Use `@vite(['resources/css/app.css', 'resources/js/app.js'])` in the `<head>`
  — that's what injects your built CSS/JS.
- Use Tailwind utility classes for styling — no separate CSS file needed.

---

## 5. Run the tests

Laravel ships with a test suite. Run it before every push:

```bash
php artisan test
```

Expect: `Tests: 2 passed (2 assertions)`.

> The feature test uses `withoutVite()` so it passes on a fresh checkout
> (before the CSS has been built). This was a real fix applied to this repo —
> without it, the test 500s because `public/build/` doesn't exist yet.

---

## 6. Create the GitHub repo and push

1. Go to [github.com/new](https://github.com/new).
2. Name it (e.g. `my-hospital`), keep it **Public** (free unlimited Actions
   minutes) or Private, click **Create repository**.
3. GitHub shows commands — copy the "…or push an existing repository" block
   and run it in your project folder:

```bash
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/<your-username>/my-hospital.git
git push -u origin main
```

Refresh the GitHub page — your code is now on GitHub. ✅

---

## 7. Deploy to Vercel — the container way (what this repo uses)

Vercel is a hosting platform. It can host **static sites** and **serverless
functions**, and since late 2024 it can host **containers** — any app you can
put in a Docker image. PHP/Laravel needs the container runtime, which is
Vercel's official path for PHP.

### 7a. The 4 files that make it work

Copy these into your project root (they're in this repo — copy them):

**`Dockerfile.vercel`** — the recipe for your production image. Three stages:
1. Composer installs production PHP dependencies.
2. Node builds your Tailwind/Vite assets.
3. FrankenPHP (PHP + the Caddy web server in one) runs the app. It creates the
   SQLite database, runs migrations, and caches views at build time.

**`Caddyfile`** — tells FrankenPHP to serve `public/` and run PHP:

```caddyfile
:{$PORT:80} {
    root * /app/public
    encode zstd gzip
    php_server
}
```

**`vercel.json`** — tells Vercel "this is a container project":

```json
{
    "services": {
        "api": {
            "root": ".",
            "entrypoint": "Dockerfile.vercel",
            "runtime": "container"
        }
    },
    "rewrites": [
        { "source": "/(.*)", "destination": { "service": "api" } }
    ]
}
```

**`.dockerignore`** — keeps secrets and junk out of the image (`.env`,
`vendor/`, `node_modules/`, `storage/`, `tests/`).

**Plus one line in `bootstrap/app.php`** — trust forwarded headers so asset
URLs stay `https` behind Vercel's edge (this fixed the CSS-not-loading bug in
this repo):

```php
$middleware->trustProxies(at: '*');
```

### 7b. Test the image locally (optional but highly recommended)

```bash
docker build -f Dockerfile.vercel -t my-hospital .
docker run --rm -p 8090:80 \
  -e "APP_KEY=$(php -r 'echo "base64:".base64_encode(random_bytes(32));')" \
  my-hospital
# open http://localhost:8090 in your browser (Mac: `open`, Linux: `xdg-open`)
```

This builds **exactly** what Vercel will build — composer install, npm build,
migrations — and runs it in the same FrankenPHP runtime. If it works here, it
works on Vercel.

### 7c. Deploy — two ways, pick ONE

There are two ways to deploy. **Do not enable both** or you'll get double
deployments. For a beginner, **Option A is easier** (zero secrets, everything
automatic).

| | Option A: Git integration | Option B: CLI + GitHub secrets |
| --- | --- | --- |
| Effort | ~3 minutes in the dashboard | ~10 minutes + token + secrets |
| Secrets needed | **None** | 3 GitHub secrets |
| Auto-deploys on push | ✅ | ✅ (via workflow) |
| Preview URLs per PR | ✅ | ✅ (via workflow) |
| Deploy trigger | Vercel watches GitHub | GitHub Actions calls Vercel |
| Best for | Beginners, most projects | When you want deploys fully inside your workflows |

#### Option A — Vercel Git integration (recommended) 🏆

Vercel watches your GitHub repo and deploys automatically — no tokens, no
secrets, no command line:

1. Open [vercel.com](https://vercel.com) logged in as **your** account.
2. Click **Add New → Project** (top-right).
3. **Import Git Repository** → find `my-hospital` → **Import**.
   - Vercel will ask for permissions the first time — allow it.
   - Framework preset: choose **Other** (your `vercel.json` already says
     "container runtime"; don't pick a PHP/Laravel preset — that's a different,
     serverless setup).
   - Click **Deploy**. Vercel reads `Dockerfile.vercel` and builds it.
4. After the first deploy, set the **Production Branch**:
   Project → **Settings → Git → Production Branch** → type `production`
   → **Save**. Now only pushes to `production` go live; `main` stays a dev
   branch.
5. Add the environment variables: Project → **Settings → Environment
   Variables** → add for **Production, Preview, Development**:

   ```
   APP_KEY   = your APP_KEY from .env (keep the SAME value forever)
   APP_ENV   = production
   APP_DEBUG = false
   APP_URL   = https://<your-project>.vercel.app
   ```

   > **Why APP_KEY?** Laravel encrypts cookies with it and refuses to boot
   > without it. Changing it breaks sessions/CSRF.

6. Open your URL (e.g. `https://my-hospital.vercel.app`) — **live**. 🎉

That's the whole setup. From now on: **push to `production` → auto-deploy,
open a PR → free preview URL**. The GitHub Actions `deploy.yml` workflow in
this repo stays safely skipped (it has no token) — leave it that way.

#### Option B — CLI + GitHub secrets (the workflow way)

If you'd rather have GitHub Actions do the deploying (and you'll add the
`VERCEL_TOKEN` secret), this is the manual one-time setup:

```bash
npx vercel login      # opens your browser — sign in (pick your account)
npx vercel link --yes # creates/links the project on Vercel
```

Add the environment variables (Laravel needs these at runtime):

```bash
npx vercel env add APP_KEY production       # paste your APP_KEY from .env
npx vercel env add APP_ENV production       # production
npx vercel env add APP_DEBUG production     # false
npx vercel env add APP_URL production       # https://<your-project>.vercel.app
```

Deploy once to verify:

```bash
npx vercel deploy --prod
```

Then add the 3 GitHub secrets (Settings → Secrets → Actions) so the
`deploy.yml` workflow can deploy on every `production` push:

```
VERCEL_TOKEN      → Vercel → Settings → Tokens → Create Token
VERCEL_ORG_ID     → from .vercel/project.json → "orgId"
VERCEL_PROJECT_ID → from .vercel/project.json → "projectId"
```

---

## 8. Add CI/CD (GitHub Actions)

CI/CD = "every time you push, the computer runs your tests and deploys for
you." Add two workflow files under `.github/workflows/` in your repo:

**`.github/workflows/ci.yml`** — runs on every push/PR: PHP tests, code-style
check (Pint), frontend build, and a Docker image build so a broken Dockerfile
never ships.

**`.github/workflows/deploy.yml`** — deploys to Vercel. It reads three GitHub
secrets:

```
VERCEL_TOKEN      → Vercel → Settings → Tokens → Create Token
VERCEL_ORG_ID     → from .vercel/project.json → "orgId"
VERCEL_PROJECT_ID → from .vercel/project.json → "projectId"
```

Add them: GitHub repo → **Settings → Secrets and variables → Actions →
New repository secret**.

> The workflow is written to **skip cleanly** (exit 0, no red ❌) until you add
> the secrets, so it's safe to push before configuring anything.

### How GitHub Actions works (the mental model)

GitHub Actions is a **free CI/CD runner farm** built into GitHub. Workflows
live in `.github/workflows/*.yml` and are YAML files that say:

```yaml
on:
    push:
        branches: [production]   # WHEN does this run?

jobs:
    deploy:                      # WHAT does it do?
        runs-on: ubuntu-latest   # a fresh Linux machine
        steps:                   # a list of commands
            - run: npx vercel deploy --prod
```

When the trigger fires, GitHub spins up a machine, runs your steps, and shows
green/red in the **Actions** tab. Your repo has two workflows:

| File | Triggers on | What it runs |
| --- | --- | --- |
| `ci.yml` | push to `main`/`production`, any PR | PHP tests, Pint style check, Vite build, **Docker image build** |
| `deploy.yml` | push to `production` | Build + deploy to Vercel (production) |
| `deploy.yml` | PR **against** `production` | Preview deploy + auto-comment the URL on the PR |

Secrets (tokens) are stored in GitHub and injected into workflows as
`${{ secrets.NAME }}` — they're never visible in your code. The deploy
workflow is written to **skip cleanly** if the secret is missing, so it's safe
to push before configuring anything.

### The branch flow this repo uses

- **`main`** — development. Pushing here runs CI only (tests + build).
  Safe to push to directly.
- **`production`** — deployable. Only this branch publishes to Vercel. It
  should be **protected** so nobody can push to it directly — changes arrive
  only through reviewed pull requests.

### 🛡️ Protect `production` (merge-only — you do this once in GitHub)

Branch protection is a **GitHub setting** (needs an admin account). It makes
`production` un-pushable: direct `git push origin production` is **rejected**;
the only way in is a merged pull request.

**Exact steps:**

1. GitHub → repo → **Settings** (top tab) → **Branches** (left sidebar) →
   **Add branch ruleset** (or *Add rule*).
2. **Branch name pattern:** type `production`.
3. Turn on **Require a pull request before merging**:
   - *Require approvals* → **1**.
   - *Dismiss stale approvals* — on.
4. Turn on **Require status checks to pass before merging**:
   - Search and select your CI checks — select **PHP tests & style**,
     **Frontend build**, **Docker image build** (the job names from `ci.yml`).
5. Turn on **Require branches to be up to date before merging**.
6. Turn on **Do not allow bypassing the above settings**.
7. Click **Create**.

Now try `git push origin main:production` — GitHub **rejects** it with
"protected branch" errors. That's exactly what we want. ✅

> The same protection idea can be applied to `main` later (then you can't push
> to `main` either — everything flows through PRs).

### Ship a change (the PR-only daily workflow)

With `production` protected, the release flow becomes:

```bash
# 1. Develop on a feature branch
git checkout -b add-contact-form
# ...edit code...
git add .
git commit -m "Add contact form"
git push -u origin add-contact-form
```

# 2. GitHub shows a yellow banner: "Compare & pull request" → click it.
#    Base = production (or main), compare = your feature branch.
#    CI runs + a Vercel preview URL is commented on the PR.
# 3. A reviewer approves, checks are green → click **Merge pull request**.
# 4. The merge commits to `production` → the deploy workflow builds the
#    Docker image and deploys to Vercel automatically.

Watch it live at **github.com/<you>/<repo>/actions**. All green = you're live,
with zero manual steps and zero chance of pushing straight to production.

---

## 9. Switching to a different Vercel account

The CI/CD workflow is account-agnostic — it reads everything from secrets, so
switching accounts changes **zero code**:

1. `npx vercel login` → sign in with the **new** account.
2. `npx vercel link --yes` → create/link the project under the new account.
3. Re-add the 4 env vars (**same APP_KEY**).
4. Create a token in the new account (vercel.com/account/tokens).
5. Update the 3 GitHub secrets with the new values (org/project IDs from the
   fresh `.vercel/project.json`).
6. `git push origin main:production` → deploys to the new account.

See the "Switching to a different Vercel account" section in `README.md` for
the full steps and gotchas (old project/URL stays until deleted/transferred).

---

## 10. Production notes & gotchas

- **SQLite is ephemeral on Vercel.** It's created and migrated at image build
  time; it resets when Vercel rebuilds. Perfect for a landing page. When you
  need real data, switch to Vercel Postgres or a free Neon Postgres and set
  `DB_CONNECTION=pgsql` + `DATABASE_URL`.
- **Uploads:** use Vercel Blob (free tier) — the container filesystem is
  temporary.
- **Logs:** Vercel → Project → Logs (stderr). `LOG_CHANNEL=stderr` is set in
  the image.
- **HTTPS asset URLs:** keep `trustProxies(at: '*')` — without it, Laravel
  generates `http://` asset URLs behind Vercel's edge and the browser blocks
  them (this repo hit exactly that bug).
- **Free-tier limits:** GitHub Actions — 2,000 min/month (private) or unlimited
  (public). Vercel Hobby — includes 4 CPU-hrs + 360 GB-hrs container memory per
  month, more than enough for a low-traffic site.

---

## 11. The complete file inventory (copy everything from here)

Every file that makes this work — copy these into ANY new Laravel project and
it deploys exactly like this one. The workflows (`ci.yml`, `deploy.yml`) and
the full guides are in this repo under `.github/workflows/` — copy those too.

| File | Purpose |
| --- | --- |
| `Dockerfile.vercel` | The production image recipe (full contents below) |
| `Caddyfile` | FrankenPHP web-server config (full contents below) |
| `vercel.json` | Tells Vercel "container project" (full contents below) |
| `.dockerignore` | Keeps secrets/junk out of the image (full contents below) |
| `bootstrap/app.php` | One added line: `trustProxies(at: '*')` |
| `.github/workflows/ci.yml` | Tests + builds on every push/PR |
| `.github/workflows/deploy.yml` | Deploys `production` to Vercel |

### Dockerfile.vercel

```dockerfile
# syntax=docker/dockerfile:1

# Stage 1: Composer dependencies (production only)
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts

# Stage 2: Frontend assets (Vite build)
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY . .
RUN npm run build

# Stage 3: FrankenPHP runtime (Vercel container)
FROM dunglas/frankenphp:1-php8.4-alpine
WORKDIR /app

# APP_KEY is injected by Vercel at runtime (and overridable at build time).
ARG APP_KEY=
ENV APP_KEY=${APP_KEY} \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SESSION_SECURE_COOKIE=true

COPY --from=composer-deps /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Laravel bootstrap: writable storage, fresh SQLite file, production caches.
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && php artisan package:discover --ansi \
    && php artisan view:cache \
    && php artisan migrate --force --no-interaction

COPY Caddyfile /etc/caddy/Caddyfile

ENV PORT=80
EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
```

### Caddyfile

```caddyfile
:{$PORT:80} {
    root * /app/public
    encode zstd gzip
    php_server
}
```

### vercel.json

```json
{
    "services": {
        "api": {
            "root": ".",
            "entrypoint": "Dockerfile.vercel",
            "runtime": "container"
        }
    },
    "rewrites": [
        { "source": "/(.*)", "destination": { "service": "api" } }
    ]
}
```

### .dockerignore

```
# Secrets & local environment — never ship these into the image
.env
.env.*
!.env.example

# Local dependencies — reinstalled fresh inside the image
vendor
node_modules

# Build artifacts & caches
public/build
public/hot
storage
bootstrap/cache
*.log

# Local SQLite artifacts (untracked; the image creates its own)
database/*.sqlite*

# VCS & tooling
.git
.gitignore
.dockerignore
.idea
.husky
tests
phpunit.xml

# OS noise
.DS_Store
Thumbs.db
```

### The one line in bootstrap/app.php

In `bootstrap/app.php`, inside the `->withMiddleware(...)` closure, add:

```php
$middleware->trustProxies(at: '*');
```

### Copy checklist for a brand-new project

```bash
# 1. Fresh Laravel project (or clone this one as the base)
composer create-project laravel/laravel my-project
cd my-project

# 2. Copy the deploy files from this repo
cp <this-repo>/Dockerfile.vercel .
cp <this-repo>/Caddyfile .
cp <this-repo>/vercel.json .
cp <this-repo>/.dockerignore .
cp -r <this-repo>/.github .

# 3. Apply the one-line proxy fix in bootstrap/app.php
# 4. Local setup: cp .env.example .env && php artisan key:generate
# 5. Deploy: Option A (Vercel Git import) or Option B (CLI + secrets) — §7c
```

---

## Troubleshooting

| Problem | Fix |
| --- | --- |
| `Key file does not exist` / white error page | Run `php artisan key:generate` |
| `APP_KEY` missing on Vercel → 500 | Add `APP_KEY` env var in Vercel |
| CSS not loading in production | Confirm `trustProxies(at: '*')` in `bootstrap/app.php` |
| Tests fail on fresh checkout | Feature test needs `withoutVite()` |
| Deploy workflow "succeeds" but nothing deploys | `VERCEL_TOKEN` secret missing — the step intentionally skips |
| Container exits immediately locally | You forgot `-e APP_KEY=...` when running `docker run` |

---

That's it — from an empty folder to a live, auto-deploying website. 🚀
Copy this repo's `Dockerfile.vercel`, `Caddyfile`, `vercel.json`,
`.dockerignore`, and workflows into every future Laravel project and you're
deploying in minutes.
