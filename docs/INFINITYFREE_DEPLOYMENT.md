# InfinityFree Hosting Guide (Shena)

This guide is tailored to this repository structure and config.

## 1) Prepare files locally

1. Ensure dependencies exist in `vendor/` before upload.
   - This project needs `dompdf/dompdf` and `phpmailer/phpmailer` from `composer.json`.
   - Because InfinityFree free hosting has no Composer CLI access, upload `vendor/` with the project.
2. Copy `.env.production.example` to `.env` and fill real values.
3. Verify `.htaccess` exists at repository root.

## 2) Create InfinityFree account + domain

1. Create hosting account in InfinityFree.
2. Add your free subdomain (for example `yourdomain.epizy.com`) or custom domain.
3. Wait until domain status is active.

## 3) Create MySQL database

1. Open InfinityFree control panel.
2. Create a MySQL database and user.
3. Open phpMyAdmin from control panel.
4. Import `database/schema.sql`.

## 4) Configure environment values

Edit `.env` with InfinityFree values:

- `DEBUG_MODE=false`
- `APP_URL=https://yourdomain`
- `DB_HOST=...` (from InfinityFree panel)
- `DB_NAME=...` (from InfinityFree panel)
- `DB_USER=...` (from InfinityFree panel)
- `DB_PASS=...` (from InfinityFree panel)
- `MPESA_STK_CALLBACK_URL=https://yourdomain/public/mpesa-stk-callback.php`
- `MPESA_C2B_CALLBACK_URL=https://yourdomain/public/mpesa-c2b-callback.php`
- Set all API keys and secrets to production values.

Important:

- The app loads `.env` in `config/config.php`.
- Do not keep default credentials from `config/config.php` in production.

## 5) Upload project to web root

1. Open File Manager or use FTP.
2. Go to account web root (`htdocs`).
3. Upload project contents directly into `htdocs`.
   - `htdocs/index.php` must exist.
   - Do not nest app under `htdocs/Shena` unless you intentionally want a subfolder deployment.
4. Confirm these directories/files exist after upload:
   - `app/`, `config/`, `resources/`, `public/`, `storage/`, `vendor/`, `index.php`, `.htaccess`, `.env`

## 6) Directory permissions

Ensure writable paths:

- `storage/uploads`
- `storage/logs`

Recommended permissions: `755` first, then `775` only if writes fail.

## 7) Validate rewrite/routing

This app uses a front controller (`index.php`) and route dispatch in `Router`.

Checklist:

1. Visit homepage.
2. Visit `/login` and `/register`.
3. Confirm CSS/JS load from `/public/...`.
4. If routes return 404, verify `.htaccess` uploaded correctly and Apache rewrite is active.

## 8) Configure M-Pesa callbacks

Set callback URLs in Safaricom Daraja exactly to:

- `https://yourdomain/public/mpesa-stk-callback.php`
- `https://yourdomain/public/mpesa-c2b-callback.php`

Then perform a sandbox test and verify logs are written in `storage/logs`.

## 9) InfinityFree free-plan limitations you must plan for

1. No native server cron/CLI scheduler for scripts in `cron/`.
   - Affected scripts include payment/status and campaign jobs.
   - Use an external scheduler service to call a protected HTTP endpoint, or run jobs from another server/VPS.
2. Long-running/background workers are not supported.
3. SMTP/network behavior may vary on free hosting; test your email flow after deployment.

## 10) Post-deploy hardening

1. Keep `DEBUG_MODE=false`.
2. Use strong random values for `ENCRYPTION_KEY` and `JWT_SECRET`.
3. Rotate admin credentials and API keys.
4. Keep backups of database and uploaded files.
5. Monitor `storage/logs/error.log` and M-Pesa callback logs.

## Quick smoke test

1. Register a test member.
2. Login as member.
3. Trigger payment initiation.
4. Hit callback endpoints with test payload or sandbox flow.
5. Confirm payment status updates in database.
6. Confirm uploads and notifications work.
