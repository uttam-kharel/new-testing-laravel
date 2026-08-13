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

| Workflow              | When                               | What it does                                            |
| --------------------- | ---------------------------------- | ------------------------------------------------------- |
| `check.yml`           | every push & PR                    | PHP 8.3 tests, Pint style check, Vite production build, Docker image build (catches broken Dockerfiles before they ship) |
| `deploy-vercel.yml`   | push to `production`, PRs against it | Production deploy on push; preview deploy + URL comment on PR |

Both run on GitHub Actions free minutes. **Two deploy options — pick ONE** (see
`GETTING_STARTED.md` §7c for the full walkthrough):

- **Option A (recommended, zero secrets):** Vercel dashboard → **Add New →
  Project → Import Git Repository** → pick this repo → framework **Other** →
  Deploy. Then set Project → Settings → Git → **Production Branch** =
  `production`, and add the 4 env vars. Every push to `production` auto-
  deploys; every PR gets a free preview URL.
- **Option B (workflow-driven):** add three GitHub secrets
  (Settings → Secrets and variables → Actions):

  ```
  VERCEL_TOKEN      – Vercel → Settings → Tokens → Create
  VERCEL_ORG_ID     – from .vercel/project.json → "orgId"   (team_...)
  VERCEL_PROJECT_ID – from .vercel/project.json → "projectId" (prj_...)
  ```

  The `deploy-vercel.yml` workflow ships with **no secrets** and skips cleanly until
  you add them, so it's safe to leave untouched while Option A handles
  deployments.

## Branch flow

Two branches exist (both pushed to GitHub):

- **`development`** — where you develop. CI runs on every push/PR, but nothing deploys.
- **`production`** — deployable. Pushing to it builds the container image and
  deploys to Vercel (via the `deploy-vercel.yml` workflow + GitHub secrets).

**Protect `production`** (Settings → Branches → Add rule on `production`:
*Require a pull request + 1 approval + status checks*) so changes can only
arrive via reviewed PRs — see `GETTING_STARTED.md` §8.

```bash
# ship a change (daily flow):
git checkout -b my-feature
# ...edit, commit...
git push -u origin my-feature
# open a PR against `production` → CI runs, Vercel posts a preview URL →
# approve + merge → deploy-vercel.yml builds the image and deploys to Vercel
```

> A push straight to `development` never deploys anything — only `production` does.

### Make `development` the default branch (one-time, already done here)

GitHub's default branch is what visitors land on when they open the repo, and
GitHub **refuses to delete a branch while it's the default** — so this always
comes first:

1. GitHub → repo → **Settings → General** (left sidebar) → **Default branch** →
   click the switch icon → pick `development` → **Update** → confirm.

### Delete a branch (e.g. the old `main`)

Only possible *after* `development` is the default branch. Either way works:

- **Dashboard:** GitHub → repo → **Settings → Branches** → click the 🗑 delete
  icon next to `main`.
- **or CLI:** `git push origin :main` — then run `git remote prune origin`
  locally to drop the stale `origin/main` tracking ref.

> This repo already went through both steps — `main` is gone and `development`
> is the default. These instructions are for reference (forks, new clones, or
> if you ever rename branches again).

## Templates

- **PRs** open with `.github/PULL_REQUEST_TEMPLATE.md` — change summary, test
  checklist, and a deploy note so everyone knows a merge to `production` goes
  live.
- **Issues** use form templates: `.github/ISSUE_TEMPLATE/bug_report.yml` and
  `.github/ISSUE_TEMPLATE/feature_request.yml`.

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

## Switching to a different Vercel account (CI/CD)

The CI/CD workflow is **account-agnostic** — it never knows which account it's
under, because the org/project/token all come from GitHub secrets. Moving the
project to another account needs **zero repo changes**, just these steps
(≈ 5 minutes):

```bash
# 1. Log in with the new account (interactive)
npx vercel login

# 2. Create/link the project under the new account
#    (or dashboard → Add New → Project → Import this repo)
npx vercel link --yes

# 3. Re-add the env vars — REUSE the same APP_KEY value so
#    encrypted cookies/CSRF keep working across the switch
npx vercel env add APP_KEY production       # paste the same value as before
npx vercel env add APP_ENV production       # production
npx vercel env add APP_DEBUG production     # false
npx vercel env add APP_URL production       # https://<your-project>.vercel.app

# 4. Create a token in the new account:
#    https://vercel.com/account/tokens → Create Token
```

Then update the **three GitHub secrets** (Settings → Secrets and variables →
Actions) with the new values:

```
VERCEL_TOKEN      – the new token from step 4
VERCEL_ORG_ID     – from .vercel/project.json → "orgId"   (team_...)
VERCEL_PROJECT_ID – from .vercel/project.json → "projectId" (prj_...)
```

That's it — the next `git push origin development:production` builds and deploys to
the **new account** automatically.

Notes:
- `.vercel/` is gitignored, so the old account's IDs never leak into the repo.
- The old account's project and URL stay until you delete or transfer them
  there. If you want the same `<project>.vercel.app` URL on the new account,
  delete the old project first (or transfer it).
- Switching accounts does **not** touch the code, the Dockerfile, or the
  workflows — only the secrets change.

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

1. `git clone` this repo (or copy `Dockerfile.vercel`, `Caddyfile`, `vercel.json`, `.dockerignore`, `bootstrap/app.php` trust-proxy lines, and `.github/workflows/deploy-vercel.yml`).
2. `vercel link --yes`, add the env vars above, `npx vercel deploy --prod`.
3. Add the three GitHub secrets (or Vercel Git import) to enable auto-deploys.
