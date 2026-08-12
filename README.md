# 🌐 Shubham International Hospital

Laravel 13 + Tailwind CSS (Vite) landing site, hosted on **Vercel** via the
[container runtime](https://vercel.com/docs/services) with FrankenPHP.

**Production:** https://shubham-international-hospital.vercel.app

---

## Local development

Requires PHP 8.3+ and Node 20+.

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Database (SQLite)
touch database/database.sqlite
php artisan migrate

# 4. Run it
npm run dev        # Vite dev server (terminal 1)
php artisan serve  # Laravel (terminal 2)
```

## Testing the production container locally

The exact image Vercel builds can be tested on any machine with Docker:

```bash
docker build -f Dockerfile.vercel -t shubham-hospital .
docker run -p 8080:80 -e APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')" shubham-hospital
curl http://localhost:8080/
```

## Deploying to Vercel

The repo carries everything Vercel needs:

| File                | Purpose                                                        |
| ------------------- | -------------------------------------------------------------- |
| `Dockerfile.vercel` | Multi-stage build: composer deps → Vite assets → FrankenPHP     |
| `Caddyfile`         | Serves `public/`, binds to Vercel's `PORT`                     |
| `vercel.json`       | Declares the container service + catch-all rewrite             |
| `.dockerignore`     | Keeps `.env`, `vendor`, `node_modules` out of the image        |

### First deploy (CLI)

```bash
npm i -g vercel          # or use: npx vercel
vercel login
vercel link --yes        # links the project, creates .vercel/
vercel env add APP_KEY production     # required — Laravel boot fails without it
vercel env add APP_ENV production     # production
vercel env add APP_DEBUG production   # false
vercel env add APP_URL production     # https://<your-project>.vercel.app
vercel deploy --prod
```

> **Note:** `APP_KEY` is mandatory. Set it once (e.g.
> `base64:$(php -r 'echo base64_encode(random_bytes(32));')`) and keep it
> stable so encrypted cookies/sessions survive redeploys.

## CI/CD (100% free — no credit card)

Everything runs on free tiers: **GitHub Actions** (unlimited minutes on public
repos, 2,000 min/month on private) + **Vercel Hobby** (free forever, includes
container/Fluid compute allowances).

Workflows live in `.github/workflows/`:

| Workflow       | When                  | What it does                                                        |
| -------------- | --------------------- | ------------------------------------------------------------------- |
| `ci.yml`       | every push & PR       | PHP 8.3 tests, Pint style check, and a Vite production build        |
| `deploy.yml`   | push to `main`        | Deploys to Vercel via the CLI — **only if** you add the token below |

### Steps

1. **Push this repo to GitHub** (free account): create an empty repo, then

   ```bash
   git remote add origin https://github.com/<you>/shubham-international-hospital.git
   git push -u origin main
   ```

   CI runs automatically on every push/PR from that point on.

2. **Connect Vercel (recommended, zero secrets):** in the Vercel dashboard go to
   **Add New → Project → Import Git Repository**. Vercel then auto-deploys on
   every push to `main` and builds free preview deployments for each PR.
   *Choose this **or** the token approach below — not both.*

3. **Alternative CD via Actions:** create a free token at
   Vercel → **Settings → Tokens → Create**, then add it as a GitHub secret:
   **Settings → Secrets and variables → Actions → `VERCEL_TOKEN`**. The
   `deploy.yml` job activates and deploys on every push to `main`.

### What you get

- Tests, lint, and a production asset build on every push/PR — green checks
  before merging.
- Automatic production deploys on `main`, plus per-PR preview URLs.
- Nothing to pay: no plan upgrade, no credit card, no usage over the included
  free allowances for a site of this size.

## Production notes

- The app runs on a **container service** (Fluid compute): it scales to zero
  when idle and cold-starts on demand — first request after idle takes ~1s.
- The SQLite database is baked in at build time (`php artisan migrate`) and is
  **ephemeral** — writes are lost when the instance scales down. Fine for the
  current landing page; when the app grows real data, switch to
  [Vercel Postgres](https://vercel.com/docs/storage/vercel-postgres) and set
  `DB_CONNECTION=pgsql` + the Postgres env vars.
- Storage (`storage/`) is also ephemeral; uploads should go to
  [Vercel Blob](https://vercel.com/docs/storage/vercel-blob).
