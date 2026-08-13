# 🚀 Deployment Playbook — Token-Based CI/CD to Vercel (Option B)

This is the **complete, step-by-step guide** for deploying this app to Vercel the
**workflow-driven way** (GitHub Actions + a Vercel API token). Every step is
listed so you can replicate it on any machine, in order, with zero guesswork.

This repo uses exactly this flow: `development` for building, `production` for
what goes live.

---

## 0. How it works (the big picture)

```
 You push to `development`      → GitHub runs check.yml (tests + builds) — nothing deploys
 You merge a PR into `production` → GitHub runs deploy-vercel.yml → Vercel builds
                                    Dockerfile.vercel → your site goes live
```

`deploy-vercel.yml` talks to Vercel using **3 GitHub secrets**:

| Secret               | What it is                        | Where to get it                          |
| -------------------- | --------------------------------- | ---------------------------------------- |
| `VERCEL_TOKEN`       | Your Vercel API key               | vercel.com → Account → Tokens → Create   |
| `VERCEL_ORG_ID`      | Your team id (`team_...`)         | `.vercel/project.json` → `"orgId"`       |
| `VERCEL_PROJECT_ID`  | Your project id (`prj_...`)       | `.vercel/project.json` → `"projectId"`   |

> **Skip-by-design:** until `VERCEL_TOKEN` exists, the workflow prints a message
> and exits green without deploying. So it's safe to push before configuring
> anything — you'll never get a red ❌, just a silent skip.

---

## 1. One-time prerequisites

- A **GitHub account** with this repo (branches `development` + `production`).
- A **Vercel account** (free Hobby plan is fine) at <https://vercel.com>.
- On your machine: **Node.js 18+** (for the Vercel CLI) and **PHP 8.3+**
  (to generate the `APP_KEY`).

Check versions:

```bash
node --version
php --version
```

---

## 2. Log in to the Vercel CLI (device-code flow)

The CLI must be logged in before it can link your project or deploy.

```bash
npx vercel login
```

The terminal prints a URL like:

```
Visit https://vercel.com/oauth/device?user_code=XXXX-XXXX
```

1. Open that URL in your browser (you should already be logged into Vercel).
2. Click **Authorize** (the code is usually pre-filled).
3. The terminal confirms automatically — you don't type anything.

Verify it worked:

```bash
npx vercel whoami      # → prints your Vercel username, e.g. uttamkharel69-3890
```

> The CLI stores credentials in `~/.local/share/com.vercel.cli/auth.json`
> (newer CLI versions) or `~/.vercel/auth.json` (older). Never share that file.

---

## 3. Link the project (creates org + project IDs)

```bash
npx vercel link --yes
```

This connects the current folder to a Vercel project and writes
**`.vercel/project.json`** (gitignored, so IDs never leak into the repo):

```json
{
  "projectId": "prj_...",
  "orgId": "team_...",
  "projectName": "your-project"
}
```

If you already created the project in the dashboard, it links to it; otherwise
it creates one automatically. Write down the `orgId` and `projectId` — you need
them for the secrets in **step 5**.

---

## 4. Create a Vercel API token (dashboard only — cannot be done via CLI)

Vercel deliberately blocks programmatic token creation — it must happen in the
dashboard:

1. Go to **https://vercel.com/account/tokens**
2. Click **Create Token**
3. Name it (e.g. `github-actions-deploy`) → **Create**
4. **Copy the token immediately** — it's shown only once.

### ⚠️ Security rule: never paste tokens into chat

A token typed into any chat/transcript is compromised. If you need to hand a
token to a script or tool, save it to a **local file** instead:

```bash
# save the token to a file (paste it where XXXXXXXX is, then run:)
printf '%s' 'XXXXXXXX' > ~/Desktop/vercel_token.txt
chmod 600 ~/Desktop/vercel_token.txt
```

Then pass it from the file — e.g. `VERCEL_TOKEN=$(cat ~/Desktop/vercel_token.txt)`.
**Delete the file once the setup is done.**

---

## 5. Add the 3 GitHub secrets

GitHub → repo → **Settings → Secrets and variables → Actions → New repository
secret**. Add three secrets (paste the values, click *Add secret* each time):

```
VERCEL_TOKEN      = the token from step 4
VERCEL_ORG_ID     = team_...   (from .vercel/project.json)
VERCEL_PROJECT_ID = prj_...    (from .vercel/project.json)
```

If the `gh` CLI is installed, the same thing from a terminal:

```bash
gh secret set VERCEL_TOKEN      < ~/Desktop/vercel_token.txt
gh secret set VERCEL_ORG_ID     --body "team_..."
gh secret set VERCEL_PROJECT_ID --body "prj_..."
```

That's all GitHub needs — the workflow reads these three and nothing else.

---

## 6. Add the environment variables to Vercel

The container runtime injects these at runtime. **`APP_KEY` is mandatory** —
Laravel refuses to boot without it.

Vercel → **Project → Settings → Environment Variables** → add for **Production**
(and Preview if you want preview deploys fully working):

| Variable   | Value                                                                                 |
| ---------- | ------------------------------------------------------------------------------------- |
| `APP_KEY`  | `base64:` + 32 random bytes (generate once — see below)                               |
| `APP_ENV`  | `production`                                                                          |
| `APP_DEBUG`| `false`                                                                               |
| `APP_URL`  | `https://<your-project>.vercel.app` (your actual URL)                                 |

Generate `APP_KEY` locally:

```bash
php -r 'echo "base64:".base64_encode(random_bytes(32));'   # run once, keep the output
```

> **Keep the same `APP_KEY` forever.** It encrypts cookies/sessions; changing it
> logs everyone out and invalidates encrypted data.

You can also add env vars from the CLI:

```bash
npx vercel env add APP_KEY production      # pastes are hidden (no echo)
npx vercel env add APP_ENV production
npx vercel env add APP_DEBUG production
npx vercel env add APP_URL production
```

---

## 7. First deploy (manual, one time)

```bash
npx vercel deploy --prod --yes
```

Vercel reads `vercel.json` → sees the container service → builds
`Dockerfile.vercel` (Composer deps → Vite assets → FrankenPHP runtime) → deploys.
Your site is now live at **`https://<your-project>.vercel.app`**.

After this, you never deploy manually again — the workflow does it.

---

## 8. The daily ship flow (this is the whole workflow)

```bash
# 1. Start from development, make a feature branch
git checkout development
git checkout -b my-feature
# ...edit code, commit...

# 2. Push and open a PR against production
git push -u origin my-feature
# GitHub shows "Compare & pull request" → base: production, compare: my-feature

# 3. CI (check.yml) runs on the PR → green checks
#    deploy-vercel.yml posts a free PREVIEW URL as a PR comment

# 4. Review → Approve → Merge
#    Merge into production → deploy-vercel.yml builds + deploys → LIVE
```

Watch it happen: GitHub → **Actions** tab (the `Deploy to Vercel` run) and
Vercel → **Deployments**.

---

## 9. Verify & logs

| Where                     | What you'll see                                              |
| ------------------------- | ------------------------------------------------------------ |
| `https://<project>.vercel.app` | The live site                                          |
| Vercel → **Logs**         | Runtime output (the container logs to `stderr`, which Vercel shows) |
| GitHub → **Actions**      | `check.yml` / `deploy-vercel.yml` run history                |
| Vercel → **Deployments**  | Every deploy with build logs + status                        |

---

## 10. Security & cleanup checklist

- [ ] Tokens live only in **GitHub secrets** and **Vercel's dashboard** — never in code or chat.
- [ ] Delete the local token file after setup: `rm ~/Desktop/vercel_token.txt`
- [ ] `.vercel/` is gitignored — confirm with `git status` that `project.json` never appears.
- [ ] Rotate the token periodically: delete it in Vercel → Tokens, create a new one, update the secret.
- [ ] Protect `production` (Settings → Branches → rule on `production`: *require a PR + 1 approval + status checks*).

---

## 11. Troubleshooting

| Symptom                                            | Cause & fix                                                                              |
| -------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| Deploy workflow runs but does nothing              | Secrets missing → add the 3 secrets (step 5); the workflow skips until then               |
| Laravel error "No application encryption key"      | `APP_KEY` missing/empty → add it to Vercel env vars (step 6)                              |
| Site loads but pages return **502**                | Caddy not on `PORT` → keep `:{$PORT:80}` in the Caddyfile and `ENV PORT=80` in the Dockerfile |
| Routes other than `/` return **404**               | Document root wrong → keep `root * /app/public` + `php_server` in the Caddyfile           |
| Changes don't appear after merge                    | Check the Actions run — the merge must be *into `production`* (pushes to `development` never deploy) |
| `vercel login` prints nothing / won't start        | Run it in a normal terminal (it needs to print the device URL)                            |
| "Cannot create tokens for this app"                | Normal — tokens are dashboard-only (step 4)                                              |

---

## Copying this playbook to a different project

1. The deploy files are portable: `Dockerfile.vercel`, `Caddyfile`,
   `vercel.json`, `.dockerignore`, the `trustProxies` line in `bootstrap/app.php`,
   and `.github/workflows/deploy-vercel.yml`.
2. Repeat steps 2 → 7 with the new project's name/URL.
3. Update the three secrets to the new org/project IDs.
