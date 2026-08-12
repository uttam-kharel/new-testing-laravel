# 🌐 Shubham International Hospital

Laravel 13 + Tailwind CSS (Vite) landing site, hosted free on **Vercel** via the
[container runtime](https://vercel.com/docs/services) with FrankenPHP.

**Production:** https://shubham-international-hospital.vercel.app

---

## The stack (why this approach)

| Piece                | What it does                                                        |
| -------------------- | ------------------------------------------------------------------- |
| `Dockerfile.vercel`  | Multi-stage image: Composer (prod deps) → Vite build → FrankenPHP runtime. Creates the SQLite DB, runs migrations & caches views at build time |
| `Caddyfile`          | FrankenPHP serves `public/` with `php_server` + compression, on Vercel's `PORT` |
| `vercel.json`        | Container service (`Dockerfile.vercel`) with a catch-all rewrite to it |
| `.dockerignore`      | Keeps `.env`, `vendor`, `node_modules`, `storage`, `tests` out of the image |
| `bootstrap/app.php`  | Trusts forwarded proxy headers so asset/route URLs stay `https` behind Vercel's edge |

Why the container route over the community `vercel-php` Functions runtime?
It's Vercel's **official** path for PHP — no third-party runtime dependency, the
full PHP environment works (writable filesystem, any extension, FrankenPHP
concurrency), and the same `Dockerfile` + `Caddyfile` copy into every future
project. It runs inside the free Hobby plan's included container usage
(4 CPU-hrs + 360 GB-hrs of memory per month — plenty for a landing page).

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

Smoke-test the production image locally (fresh composer + npm installs happen
inside the build, exactly like Vercel):

```bash
docker build -f Dockerfile.vercel -t hospital-test .
docker run --rm -p 8090:80 -e APP_KEY="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')" hospital-test
curl http://localhost:8090/
```

## CI/CD (100% free)

| Workflow      | When                        | What it does                                            |
| ------------- | --------------------------- | ------------------------------------------------------- |
| `ci.yml`      | every push & PR            | PHP 8.3 tests, Pint style check, Vite production build  |
| `deploy.yml`  | push to `production`       | Production deploy to Vercel (container image build)     |

Both run on GitHub Actions free minutes. The deploy workflow ships with **no
secrets**, so it skips cleanly until you add them — activate it with three
GitHub secrets (Settings → Secrets and variables → Actions):

```
VERCEL_TOKEN      – Vercel → Settings → Tokens → Create
VERCEL_ORG_ID     – from .vercel/project.json → "orgId"   (team_...)
VERCEL_PROJECT_ID – from .vercel/project.json → "projectId" (prj_...)
```

Prefer Vercel's native Git integration (dashboard → Add New → Project → Import
Git repo) for zero-secret auto-deploys and free PR previews.

## Branch flow

- **`main`** — development. CI runs on every push/PR, but nothing deploys.
- **`production`** — deployable. Push to it (or merge `main` into it) and Vercel
  builds the container image and deploys to production.

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

- **Database:** SQLite is created and migrated at **build time** inside the
  image. It's ephemeral — it resets whenever Vercel scales the container down
  and rebuilds. Perfect for a landing page; switch to Vercel Postgres (or a
  free Neon instance) once real data arrives.
- **Uploads:** use [Vercel Blob](https://vercel.com/docs/storage/vercel-blob)
  (free tier) — the container filesystem is ephemeral.
- **Logs:** visible under Vercel → Project → Logs (stderr channel).
- **Env vars** set in the Vercel dashboard win over the image defaults.

## Copying this to a future project

1. `git clone` this repo (or copy `Dockerfile.vercel`, `Caddyfile`, `vercel.json`, `.dockerignore`, `bootstrap/app.php` trust-proxy lines, and `.github/workflows/deploy.yml`).
2. `vercel link --yes`, add the env vars above, `npx vercel deploy --prod`.
3. Add the three GitHub secrets (or Vercel Git import) to enable auto-deploys.
