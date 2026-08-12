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
open http://localhost:8090
```

This builds **exactly** what Vercel will build — composer install, npm build,
migrations — and runs it in the same FrankenPHP runtime. If it works here, it
works on Vercel.

### 7c. Deploy

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

> **Why APP_KEY?** Laravel encrypts cookies with it and refuses to boot without
> it. Keep the **same value forever** — changing it breaks sessions/CSRF.

Deploy:

```bash
npx vercel deploy --prod
```

Vercel builds your Docker image, deploys it, and gives you a URL like
`https://my-hospital.vercel.app`. Open it — your site is **live**. 🎉

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

### The branch flow this repo uses

- **`main`** — development. Pushing here runs CI only (tests + build).
- **`production`** — deployable. Pushing here builds the Docker image and
  deploys to Vercel.

```bash
git checkout -b production && git push -u origin production   # one-time setup
git checkout main
git push origin main:production                              # ship every release
```

### Ship a change (the daily workflow)

```bash
# 1. Edit your code... then:
git add .
git commit -m "Update the welcome page"
git push origin main            # CI runs
git push origin main:production # CI + build + deploy
```

Watch it live at **github.com/<you>/<repo>/actions**. All green = you're live.

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
