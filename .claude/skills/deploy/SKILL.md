---
name: deploy
description: Push and pull for misubaguna.com. Use when the user says "push", "pull", "deploy", "redeploy", "go live", or "update the live site". "push" = commit and push local work to GitHub main; "pull" = make the live Hostinger server pull from GitHub and redeploy. "push and pull" = both, in that order.
---

# Push & Pull (misubaguna.com)

The user has standing approval for this flow: when they ask to push and/or pull, do it without re-asking. Still stop and ask if something unexpected shows up (see "Stop and ask").

## Facts

- Repo: `git@github.com:bio-alt/misubaguna.git`, branch `main`
- Server: `ssh -p 65002 u427570300@151.106.114.215` with key auth (`~/.ssh/id_ed25519`). Always pass `-o BatchMode=yes`. **Never** use the password in the local `.env`. If key auth fails, ask the user to re-add the public key in hPanel → SSH Access → SSH Keys.
- App dir on server: `~/domains/misubaguna.com/misubaguna-app` (clone that pulls through deploy key `~/.ssh/misubaguna_github_deploy_ed25519`, set in `core.sshCommand`)
- Web root: `~/domains/misubaguna.com/public_html` (copy of `public/`, which `~/deploy-misubaguna.sh` produces)
- Production `.env` exists only on the server (chmod 600). Never overwrite it during a normal deploy, and never print its secrets.
- Local `.env` points at the **live** MySQL DB. Never run migrations or seeders locally unless the user asks.

## PUSH: local → GitHub

1. Run `git status --short` and `git diff --stat`. Never stage `scratch/`, `.claude/`, `.env`, or other secrets/large binaries. Stage files by name, not `git add -A`.
2. Run `php artisan test`. If anything fails, stop and report; don't push.
3. Commit with a clear message describing the change, ending with the attribution line from the current system reminder.
4. Run `git push origin main`. If the push is rejected because the remote is ahead, `git pull --rebase origin main`, re-run the tests, then push. Never force-push.
5. Report the commit hash and a one-line summary of what went up.

## PULL: GitHub → live server

1. Make sure what should go live is pushed (`git status -sb` shows nothing ahead). If local work is unpushed, mention it and ask whether to push it first, unless the user said "push and pull".
2. Deploy:
   ```bash
   ssh -o BatchMode=yes -p 65002 u427570300@151.106.114.215 "bash ~/deploy-misubaguna.sh" 2>&1 | tail -25
   ```
   The script pulls with `--ff-only`, runs `composer install --no-dev`, runs forward-only `migrate --force` (after `--pretend`), copies `public/` into `public_html`, and runs `artisan optimize`. Success ends with `DEPLOY_OK: <hash>`; confirm that hash matches local `git rev-parse --short HEAD`.
3. Verify live:
   ```bash
   for u in / /contact-us/ /about-us/ /catalog/ /services/ /toolkit/ /sitemap.xml /robots.txt; do printf "%-15s " "$u"; curl -s -o /dev/null -m 30 -w "%{http_code}\n" "https://misubaguna.com$u"; done
   ```
   All should return 200. Also spot-check any page touched by the change.
4. If the contact form/mail changed, test the POST path with the honeypot field filled (`website=bot`), which redirects with success but sends no email. Only send a real inquiry to marketing@misubaguna.com if the user asks.
5. Report: deployed hash, migrations run (if any), and page check results.

## Stop and ask

- The deploy script's `migrate --pretend` shows destructive SQL (DROP, column removal, truncation).
- `git pull --ff-only` fails on the server (the server checkout diverged). Don't reset it; show `git status` and ask.
- Any live page returns 5xx after deploy. Check `~/domains/misubaguna.com/misubaguna-app/storage/logs/laravel.log` (tail only) and report before changing anything.
- The app dir or `public_html` is missing (the site was wiped). Rebuild by cloning into the app dir with the deploy key, restoring a production `.env` (APP_ENV=production, APP_DEBUG=false, APP_URL=https://misubaguna.com, LOG_LEVEL=error; built from local `.env` without the SSH comment block, uploaded with scp, chmod 600), running `composer install`, then the deploy script. Confirm with the user before recreating `.env`.
