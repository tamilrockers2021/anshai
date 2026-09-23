# Ansh AI — Hostinger Deployment Guide

Step-by-step setup for **Hostinger shared hosting (hPanel)**. Follow in order.
The same steps work on VPS/other Apache/LiteSpeed hosts — only the panel screens differ.

---

## 0. Fixing your CURRENT error first (important)

You hit two errors, both now fixed in the code:

1. **`Duplicate entry 'free' for key 'uq_plans_slug'`** during install.
2. **`काहीतरी चूक झाली`** right after "successful" install.

Your first install partially seeded the database but never finished writing
`config/config.php`, so re-running the installer replayed the seed data and
collided. The seed statements are now idempotent (`INSERT IGNORE`), so this can't
happen again — **but your database still contains the half-seeded tables from the
failed run.** The clean, reliable recovery is to start the database empty:

**Recovery steps**
1. In hPanel → **Databases → phpMyAdmin**, open your database.
2. Select **all tables → Drop** (or just delete the database and create a fresh
   empty one with the same name). This throws away only the broken half-install —
   you have no real users yet.
3. On the server (File Manager), delete `config/config.php` if it exists
   (so the installer is unlocked).
4. Re-upload the **updated code** (this build), then run the installer (Section 4).

> If you prefer not to drop the DB: the updated `database.sql` is now safe to
> re-run, but a fresh empty DB guarantees all unique keys are created correctly.

---

## 1. Requirements (set these in hPanel)

- **PHP 8.2 or newer.** hPanel → **Advanced → PHP Configuration → PHP version** → select 8.2+.
- In **PHP Configuration → PHP extensions**, confirm these are ON:
  `pdo_mysql`, `openssl`, `curl`, `mbstring`.
- **MySQL 8 / MariaDB 10.4+** (Hostinger default is fine).
- **Free SSL** (Let's Encrypt) — hPanel → **Security → SSL**. Enable it and wait
  until it says *Active* **before** visiting the site (see the ⚠️ note in Section 3).

---

## 2. Create the database

hPanel → **Databases → MySQL Databases → Create**:

1. Create a **new database** (e.g. `ansh_ai`).
2. Create a **database user** and a strong password.
3. **Assign the user to the database** with *All Privileges*.
4. Write down: **database name, user, password**.
   - **DB Host on Hostinger shared hosting is `localhost`** (not `127.0.0.1`).

---

## 3. Upload the files

**Option A — File Manager (easiest)**
1. Zip the whole project folder locally.
2. hPanel → **Files → File Manager** → open **`public_html`**.
3. Upload the zip **into `public_html`** and **Extract** it there.
4. Make sure the files sit *directly* in `public_html` — i.e.
   `public_html/index.php`, `public_html/admin/`, `public_html/includes/` …
   **not** `public_html/ansh-ai/index.php`. Move them up one level if needed.

**Option B — FTP** (FileZilla): upload the project contents into `public_html`.

> ⚠️ **SSL ordering (avoids a redirect loop / "site can't be reached"):**
> The included `.htaccess` forces HTTPS. If SSL is **not** active yet, the site
> will redirect to `https://` and fail. So either:
> - enable Hostinger SSL **first** (Section 1), **or**
> - temporarily comment out the 3 "Force HTTPS" lines in `public_html/.htaccess`
>   (lines under `# Force HTTPS`), run the installer over http, then re-enable
>   them once SSL is Active.

---

## 4. Run the installer

1. Visit **`https://your-domain.com/admin/install.php`**.
2. The page shows a **system check** (PHP version, extensions, `config/` writable).
   All must be ✅. If `config/ लिहिण्यायोग्य` is ❌, set the `config` folder to
   permission **755** in File Manager (right-click → Permissions) and reload.
3. Fill in:
   - **App URL**: `https://your-domain.com` (no trailing slash).
   - **Environment**: `production`.
   - **Database**: Host `localhost`, Port `3306`, plus the name/user/password from Section 2.
   - **First Super Admin**: your name, email, and a password (min 8 chars — you choose it).
4. Click **स्थापित करा (Install)**. On success you'll see "स्थापना यशस्वी!".

The installer imports the schema, generates the `APP_KEY`, writes
`config/config.php`, creates your admin, and **locks itself**.

---

## 5. Lock down the installer (do this immediately)

After a successful install, prevent anyone from re-running it:

- In File Manager, **delete `admin/install.php`** (recommended), **or** rename it.
- The installer also self-locks (returns 403 while `INSTALLED=true`), but deleting
  it is the safe belt-and-suspenders step.

---

## 6. Configure Gemini / PayU / SMTP (in the admin panel)

Log in at **`https://your-domain.com/admin/login.php`** with the super-admin you created.

- **AI Settings** → paste your **Google Gemini API key**, pick a model
  (`gemini-1.5-flash` is a good default), then click **Test Gemini**.
- **Payment Settings** → PayU **Merchant Key + Salt**, choose `test` or `production`,
  set prices. (Server verifies the PayU response hash — safe.)
- **SMTP Settings** → host, port, encryption, username, password, from-name/email,
  then **Test SMTP** to a real inbox.
  - The password field stays blank on reload and shows `••••` — **leave it blank to
    keep the saved password**; only type to change it.

> **About email:** registration/verification emails send through these SMTP
> settings. If **Test SMTP** works, registration email now works too (a bug where
> the mailer class wasn't loaded on the registration/verification pages has been
> fixed). If email ever fails, the app now says so honestly and offers "resend"
> instead of pretending it was sent.

---

## 7. Cron jobs (subscription expiry, reminders, cleanup)

hPanel → **Advanced → Cron Jobs**. Find your PHP CLI path first (hPanel usually
provides it; commonly `/usr/bin/php` or a versioned path like
`/opt/alt/php82/usr/bin/php`). Add:

```
0 1 * * *   /usr/bin/php /home/USERNAME/domains/your-domain.com/public_html/cron/expire_subscriptions.php
0 9 * * *   /usr/bin/php /home/USERNAME/domains/your-domain.com/public_html/cron/send_expiry_reminders.php
30 1 * * *  /usr/bin/php /home/USERNAME/domains/your-domain.com/public_html/cron/cleanup_tokens.php
0 2 * * *   /usr/bin/php /home/USERNAME/domains/your-domain.com/public_html/cron/cleanup_logs.php
```

Replace `USERNAME` and the path with your real absolute path (check File Manager
breadcrumb). **No CLI access?** Set a `cron_secret` value in Admin → Settings and
call each script over HTTPS with `?token=YOUR_SECRET` from an external cron service.

---

## 8. Go-live verification checklist

- [ ] `https://your-domain.com/` loads the landing page (no "काहीतरी चूक झाली").
- [ ] Register a test account → verification email arrives → link verifies → chat opens.
- [ ] Admin → **Test Gemini** and **Test SMTP** both succeed.
- [ ] Send a chat message and get a Marathi reply.
- [ ] `https://your-domain.com/admin/install.php` is **deleted/blocked** (404/403).
- [ ] Visiting `https://your-domain.com/config/config.php` is **denied** (403).
- [ ] SSL padlock shows on all pages; `http://` redirects to `https://`.

---

## Troubleshooting

| Symptom | Cause / Fix |
|--------|-------------|
| `Duplicate entry ... uq_plans_slug` on install | Half-seeded DB from an earlier run → drop all tables / recreate the DB, delete `config/config.php`, re-run installer (Section 0). |
| `काहीतरी चूक झाली` after install | Fixed in this build (mailer/UI classes now load globally). Re-upload the updated code. Check `storage/logs/php_errors.log` for details. |
| Redirect loop / "can't reach site" | SSL not Active yet but HTTPS redirect is on → enable Hostinger SSL, or temporarily comment the "Force HTTPS" lines in `.htaccess` (Section 3). |
| `config/ लिहिण्यायोग्य ❌` in installer | Set `config` folder permission to 755 in File Manager. |
| Emails don't send but Test SMTP works | Re-upload the updated code (registration/verification email sending is fixed). |
| Blank white page | Set `APP_ENV=development` temporarily in `config/config.php`, reload to see the error, then read `storage/logs/php_errors.log`. Set it back to `production` afterward. |

**Where logs live:** `storage/logs/php_errors.log` (PHP fatals) and the app log.
Both are blocked from the web by `.htaccess`.
