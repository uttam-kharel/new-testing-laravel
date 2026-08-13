# ▶️ Start Here — from zero to deployed

One file to get from "nothing installed" to "site is live on Vercel". Follow it
top to bottom. Deeper docs: `README.md` (overview), `GETTING_STARTED.md`
(beginner tutorial), `DEPLOYMENT.md` (full deployment playbook).

---

## 1. What this is

A **Laravel 13 + Tailwind CSS** landing site that runs on **Vercel's container
runtime** (official PHP path): Vercel builds `Dockerfile.vercel` (Composer →
Vite → FrankenPHP) and serves it via the `Caddyfile`.

Two branches only:

| Branch         | Role                                             | Deploys? |
| -------------- | ------------------------------------------------ | -------- |
| `development`  | Where you build. CI runs on every push/PR.       | ❌ Never |
| `production`   | What goes live. Merges here auto-deploy to Vercel.| ✅ Yes   |

---

## 2. First time on a machine

```bash
# prerequisites: PHP 8.3+, Node 20+, Composer
git clone <your-repo-url>
cd <project-folder>
git checkout development

composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install

npm run dev        # terminal 1 — Vite
php artisan serve  # terminal 2 — Laravel at http://localhost:8000
```

> Never commit `.env`, `.vercel/`, or `.freebuff/` — they're gitignored.

---

## 3. CI/CD at a glance (GitHub Actions, 100% free)

| Workflow              | When                                            | Does what                                   |
| --------------------- | ----------------------------------------------- | ------------------------------------------- |
| `check.yml`           | every push & PR                                 | PHP tests, Pint style, Vite build, Docker image build |
| `deploy-vercel.yml`   | push to `production`, PRs against it            | Production deploy; preview URL comment on PRs |

Rules of thumb:

- Push to **`development`** → checks run, **nothing deploys**.
- Merge into **`production`** → Docker image builds on Vercel → **site updates**.
- PR against `production` → CI + a free **preview URL** posted as a comment.

---

## 4. Deploy to Vercel (token way — what this repo uses)

### 4a. One-time accounts
- GitHub repo (this one) and a free **Vercel** account.

### 4b. Log in the CLI and link the project
```bash
npx vercel login       # prints a URL → open it in your browser → click Authorize
npx vercel whoami      # confirm you're logged in (shows your username)
npx vercel link --yes  # creates/links the project → writes .vercel/project.json
```
Write down the `orgId` (`team_...`) and `projectId` (`prj_...`) from
`.vercel/project.json` — you need them below.

### 4c. Create an API token (dashboard only)
1. **https://vercel.com/account/tokens** → **Create Token** → name it → copy it.
2. **Never paste tokens in chat.** If you must hand it to a script, save it to a
   local file: `printf '%s' 'TOKEN' > ~/Desktop/vercel_token.txt` and delete the
   file when done.

### 4d. Add 3 GitHub secrets
GitHub → repo → **Settings → Secrets and variables → Actions**:

```
VERCEL_TOKEN      = the token from 4c
VERCEL_ORG_ID     = team_...   (from .vercel/project.json)
VERCEL_PROJECT_ID = prj_...    (from .vercel/project.json)
```

The `deploy-vercel.yml` workflow **skips cleanly until these exist** — no red ❌.

### 4e. Add the environment variables
Vercel → **Project → Settings → Environment Variables** (add for Production and
Preview):

| Variable    | Value                                                        |
| ----------- | ------------------------------------------------------------ |
| `APP_KEY`   | `base64:` + 32 random bytes — run once: `php -r 'echo "base64:".base64_encode(random_bytes(32));'` (keep it forever) |
| `APP_ENV`   | `production`                                                 |
| `APP_DEBUG` | `false`                                                      |
| `APP_URL`   | `https://<your-project>.vercel.app`                          |

### 4f. First deploy (one time, then never again)
```bash
npx vercel deploy --prod --yes
```
Your site is live at **`https://<your-project>.vercel.app`**. From now on the
workflow deploys for you.

---

## 5. Daily workflow (ship a change)

```bash
git checkout development
git checkout -b my-feature
# ...edit, commit...
git push -u origin my-feature
```
Open a **PR against `production`** → CI runs → Vercel posts a preview URL →
approve → merge → **auto-deploy to production**.

Watch it live: GitHub → **Actions** tab, and Vercel → **Deployments / Logs**.

---

## 6. Security rules (always)

- Tokens live only in **GitHub secrets** and **Vercel's dashboard** — never in code, never in chat.
- Delete the local token file after setup: `rm ~/Desktop/vercel_token.txt`
- `.vercel/` and `.env*` stay gitignored — check `git status` occasionally.
- Protect `production`: GitHub → **Settings → Branches** → rule on `production`
  → *Require a pull request + 1 approval + status checks*.

---

## 7. Troubleshooting (quick)

| Problem                                              | Fix                                                     |
| ---------------------------------------------------- | ------------------------------------------------------- |
| Deploy workflow runs but doesn't deploy              | Add the 3 GitHub secrets (4d)                           |
| "No application encryption key"                      | Add `APP_KEY` to Vercel env vars (4e)                   |
| 502 errors                                           | Caddyfile must keep `:{$PORT:80}`                       |
| 404 on routes other than `/`                         | Caddyfile must keep `root * /app/public` + `php_server` |
| Changes not appearing after merge                    | The merge must be *into `production`*                   |
| `vercel login` seems stuck                           | Run it in a normal terminal; open the printed URL       |

---

## 8. Where to go deeper

- **`README.md`** — architecture, stack choices, production notes.
- **`GETTING_STARTED.md`** — the full beginner tutorial (Tailwind, tests, branch protection).
- **`DEPLOYMENT.md`** — the complete token-based deploy playbook with every click and command.
