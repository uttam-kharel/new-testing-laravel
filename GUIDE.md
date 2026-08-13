# 📖 The Complete Guide — from absolute zero to a live website

This is the **one guide** that teaches you everything about this project: what
it is, how to run it on your computer, how git and GitHub work, why there are
two branches, how CI/CD works, and how it all ends up live on **Vercel**.

Read it top to bottom — every section ends with a ✅ checkpoint so you always
know you're on track. No jargon is left unexplained (see the **Glossary** at
the end if a word ever trips you up).

> Reference docs: `README.md` (overview), `GETTING_STARTED.md` (tutorial),
> `DEPLOYMENT.md` (deployment details).

---

## 0. What you're looking at

This is a **Laravel** (PHP) website — a hospital landing page — with a
**Tailwind CSS** frontend. It's hosted on **Vercel** using Vercel's *container
runtime*: Vercel builds a Docker image from `Dockerfile.vercel` and serves it
with **FrankenPHP** (a web server with PHP built in).

The file map you actually need to care about:

| File/folder          | What it is                                              |
| -------------------- | ------------------------------------------------------- |
| `routes/web.php`     | The web page routes (this site has `/` → welcome page)  |
| `resources/views/`   | HTML templates (the welcome page lives here)            |
| `resources/css/`     | Tailwind CSS                                             |
| `Dockerfile.vercel`  | Tells Vercel how to build the server image               |
| `Caddyfile`          | The web server config inside the container               |
| `vercel.json`        | Tells Vercel to use the container service                |
| `.github/workflows/` | GitHub Actions: `check.yml` (tests) + `deploy-vercel.yml` (deploy) |
| `.env`               | Your secret settings (never commit this!)               |

---

## 1. Install the tools (one time per computer)

| Tool         | Why you need it                | Check it's installed with |
| ------------ | ------------------------------ | ------------------------- |
| **PHP 8.3+** | Runs Laravel                   | `php --version`           |
| **Composer** | Installs PHP packages          | `composer --version`      |
| **Node.js 20+** | Runs the frontend build (Vite) | `node --version`        |

Not installed? Download:
- PHP + Composer → <https://windows.php.net> (Windows), `brew install php composer` (Mac),
  `sudo apt install php composer` (Ubuntu).
- Node.js → <https://nodejs.org> (the LTS version).

✅ You can run `php --version`, `composer --version`, and `node --version`.

---

## 2. Get the code onto your computer

```bash
git clone <your-repo-url>      # e.g. https://github.com/uttam-kharel/new-testing-laravel
cd <project-folder>
git checkout development       # always work on the development branch
```

✅ You have the folder and you're on `development` (`git branch` shows it).

---

## 3. Run it locally

```bash
composer install               # install PHP packages (reads composer.json)
cp .env.example .env           # create your private settings file
php artisan key:generate       # create the encryption key Laravel needs
touch database/database.sqlite # create the (empty) database file
php artisan migrate            # create the database tables
npm install                    # install frontend packages
```

Now start two things in **two separate terminals**:

```bash
npm run dev          # terminal 1 — the Vite frontend builder
php artisan serve    # terminal 2 — Laravel at http://localhost:8000
```

✅ Open **http://localhost:8000** — you see the welcome page.

---

## 4. Git in 10 minutes

Git tracks *versions* of your code. GitHub is a website that stores your git
repository in the cloud.

The commands you'll use daily:

| Command | What it does |
| ------- | ------------ |
| `git status` | Shows what changed |
| `git add <file>` | Stage a change (tell git "track this") |
| `git commit -m "message"` | Save a snapshot with a message |
| `git push` | Upload your commits to GitHub |
| `git pull` | Download others' commits |
| `git checkout -b name` | Create + switch to a new **branch** |
| `git branch` | List your branches |

A **branch** is just a separate line of work. You experiment on a branch, then
merge it into a main line when it's good.

✅ You can `git add`, `git commit -m "hi"`, and `git push` on a test branch.

---

## 5. The two branches (the heart of this project)

```
development  ← you build here. Push whenever you like. CI runs. NOTHING deploys.
production   ← what the world sees. Only reviewed PRs can touch it. Merges deploy.
```

- **`development`** — your playground. Pushing here is always safe because it
  can never affect the live site.
- **`production`** — the live site. It's **protected**: GitHub rejects direct
  pushes, requires a pull request, **1 approval**, and green CI checks before
  anything merges. Merging *into* it triggers the deploy.

✅ You understand: "push to development = safe, merge to production = live."

---

## 6. CI/CD explained simply

**CI (Continuous Integration)** — a robot checks every change before it ships:
`.github/workflows/check.yml` runs on every push/PR and does:
1. PHP tests + code-style check (Pint)
2. Frontend production build (Vite)
3. A full Docker image build (proves Vercel can build it)

If any step fails → red ❌ → you fix it before anything ships.

**CD (Continuous Deployment)** — a robot ships approved changes:
`.github/workflows/deploy-vercel.yml`:
- Merge into `production` → builds the Docker image on Vercel → **site updates**.
- PR against `production` → also creates a free **preview URL** (a throwaway
  copy of the site) and posts it as a comment on the PR.

The deploy workflow talks to Vercel using 3 **GitHub secrets** (hidden values
you never see in code): `VERCEL_TOKEN`, `VERCEL_ORG_ID`, `VERCEL_PROJECT_ID`.

✅ You can explain what happens when you push to `development` vs merge to `production`.

---

## 7. Deploy to Vercel

**Already done for this project** — the site is live at
**https://new-test-live.vercel.app**. Here's what a full setup looks like, so
you can redo it anywhere (all details in `DEPLOYMENT.md`):

```bash
npx vercel login          # prints a URL → open it in your browser → Authorize
npx vercel whoami         # confirm: prints your username
npx vercel link --yes     # connects the folder to a Vercel project
```

Then, in the dashboards:
1. **Vercel** → <https://vercel.com/account/tokens> → **Create Token** (name it
   `github-actions-deploy`) → copy it. *(Never paste tokens in chat — save to a
   local file if you must, and delete the file after.)*
2. **GitHub** → repo → **Settings → Secrets and variables → Actions** → add:
   `VERCEL_TOKEN`, `VERCEL_ORG_ID` (`team_...` from `.vercel/project.json`),
   `VERCEL_PROJECT_ID` (`prj_...` from `.vercel/project.json`).
3. **Vercel** → Project → **Settings → Environment Variables** → add:
   `APP_KEY` (run once: `php -r 'echo "base64:".base64_encode(random_bytes(32));'`),
   `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<your-project>.vercel.app`.
4. First deploy: `npx vercel deploy --prod --yes`. After that, the workflow
   deploys for you.

✅ You know where the site lives, what the 3 secrets are, and what the 4 env vars are.

---

## 8. Your daily workflow (ship a change)

```bash
# 1. Start from the latest development
git checkout development
git pull

# 2. Make a feature branch
git checkout -b my-feature
# ...edit code...

# 3. Save + upload your work
git add .
git commit -m "feat: add contact section"
git push -u origin my-feature

# 4. On GitHub: "Compare & pull request" → base: production, compare: my-feature
#    CI runs → Vercel posts a preview URL on the PR

# 5. Ask someone to review → they Approve → click Merge pull request
#    → deploy-vercel.yml builds and deploys → LIVE
```

✅ You can ship a change end to end: feature branch → PR → merge → live.

---

## 9. Security habits (make these automatic)

- **Never** commit `.env`, `.vercel/`, or `.freebuff/` (they're gitignored — check
  with `git status`).
- **Never** paste passwords or tokens into chat, code, or commits.
- Tokens live only in **GitHub secrets** and the **Vercel dashboard**.
- Delete local token files after setup.
- The `production` branch is protected — keep it that way.

---

## 10. Troubleshooting

| Symptom | Fix |
| ------- | --- |
| `composer` or `npm` command not found | Install the tools (Section 1) |
| `No application encryption key` | Run `php artisan key:generate` (or set `APP_KEY` on Vercel) |
| Blank page / 500 locally | Check the terminal running `php artisan serve` for errors |
| CI shows red ❌ | Click the failed job → read the log → fix → push again |
| Deploy workflow runs but doesn't deploy | Secrets missing — add the 3 GitHub secrets (Section 7) |
| Site shows 502 on Vercel | Keep `:{$PORT:80}` in `Caddyfile` and `ENV PORT=80` in the Dockerfile |
| Site shows 404 for routes | Keep `root * /app/public` + `php_server` in `Caddyfile` |
| Changes not appearing live | The merge must be *into `production`*, not `development` |
| `vercel login` seems stuck | Run in a normal terminal; open the printed URL in your browser |

---

## 11. Glossary

| Term | Meaning |
| ---- | ------- |
| **Repository / repo** | The folder of code + its full history |
| **Commit** | A saved snapshot of your code |
| **Branch** | A separate line of work |
| **Merge** | Combine a branch into another |
| **Pull request (PR)** | A proposed change, reviewed before merging |
| **CI** | Automated checks on every change |
| **CD** | Automated deployment of approved changes |
| **Secret** | A hidden value (like a token) stored by GitHub/Vercel |
| **Env var** | A setting injected into the app at runtime (like `APP_KEY`) |
| **Container** | A packaged app + its server, built from a Dockerfile |
| **Dockerfile** | The recipe for building the container |
| **Vercel** | The hosting platform that runs the container and gives you a URL |
| **FrankenPHP** | The web server + PHP runtime inside the container |

---

*Next steps: practice the daily workflow (Section 8) on a real change. When
you're comfortable, read `DEPLOYMENT.md` to understand the deployment in depth.*
