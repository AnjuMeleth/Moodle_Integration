# CertifyMe Moodle Plugin — Deployment Guide

**Plugin:** `local_certifyme` | **Version:** 2.0.0 | **May 2026**

---

## Step 1 — Test the API First

Before touching Moodle, confirm your token and template work:

```bash
curl -X POST https://apac.platform.certifyme.dev/api/v2/credential \
  -H "Authorization: YOUR_API_TOKEN" \
  -H "Content-Type: application/json" \
  -H "accept: application/json" \
  -d '{
    "name": "Test Student",
    "email": "your@email.com",
    "template_ID": "YOUR_TEMPLATE_ID",
    "text": "",
    "verify_mode": "None"
  }'
```

Expected: HTTP 200 + credential appears in your CertifyMe dashboard.
Replace the URL with your server if not on APAC.

---

## Step 2 — Run Moodle Locally (Docker)

```bash
docker run -d \
  -p 8080:8080 \
  -e MOODLE_USERNAME=admin \
  -e MOODLE_PASSWORD=Admin1234! \
  --name moodle \
  bitnami/moodle:latest
```

Wait ~3 minutes, then open **http://localhost:8080** and log in.

---

## Step 3 — Install the Plugin

```bash
# Copy plugin into the running container
docker cp /home/harshit/Desktop/certifyme-codes/Moodle_Integration \
  moodle:/bitnami/moodle/local/certifyme
```

In Moodle browser:
1. **Site Administration → Notifications** → click **Upgrade Moodle database now**
2. **Site Administration → Plugins → Local Plugins → CertifyMe**
3. Select your server, fill in API Token and Template ID
4. Fill in Custom Fields matching your CertifyMe template (one per line: `FieldName=value`)
5. Click **Save changes**

---

## Step 4 — End-to-End Test

1. Create a test course with completion tracking enabled
2. Enrol a test student (use a real email)
3. Mark the student as complete
4. Run the cron to fire the event:
   ```bash
   docker exec moodle php /bitnami/moodle/admin/cli/cron.php
   ```
5. Check CertifyMe dashboard — credential should appear
6. Check student email — credential email should arrive

**Switch server in settings → repeat test → confirm credential lands on the correct server.**

---

## Step 5 — Pre-Submission Checklist

- [ ] Plugin installs on clean Moodle 4.x with zero errors
- [ ] Settings page shows all four servers in the dropdown
- [ ] Course completion triggers the observer (check debug logs)
- [ ] API returns HTTP 200 on each server tested
- [ ] Credential appears on the correct server dashboard
- [ ] Student receives credential email
- [ ] Tested on MySQL and PostgreSQL
- [ ] No PHP errors with debug mode on
- [ ] Every PHP file has the GPL v3 license header

**Enable debug mode:** Site Administration → Development → Debugging → set to DEVELOPER

**Watch logs:**
```bash
docker exec moodle tail -f /opt/bitnami/apache/logs/error_log
```

---

## Step 6 — Push to GitHub

```bash
cd /home/harshit/Desktop/certifyme-codes/Moodle_Integration

git remote set-url origin https://github.com/certifyme/moodle-local_certifyme.git
git add .
git commit -m "v2.0.0 — multi-server local plugin"
git tag v2.0.0
git push origin main --tags
```

Repo requirements: **Public**, name exactly `moodle-local_certifyme`, **Issues enabled**, license **GPL v3**.

---

## Step 7 — Create ZIP for Submission

```bash
cd /home/harshit/Desktop/certifyme-codes

cp -r Moodle_Integration certifyme
zip -r moodle-local_certifyme-v2.0.0.zip certifyme --exclude "*.git*"
rm -rf certifyme
```

The folder inside the ZIP must be named `certifyme`.

---

## Step 8 — Submit to Moodle Directory

1. Log in at **https://moodle.org/login**
2. Go to **https://moodle.org/plugins/registerplugin.php**
3. Fill in:
   - Plugin name: `CertifyMe`
   - Type: `Local`
   - GitHub URL: `https://github.com/certifyme/moodle-local_certifyme`
   - Bug tracker: `https://github.com/certifyme/moodle-local_certifyme/issues`
4. Upload the ZIP + screenshots of the settings page and a issued credential
5. Submit — review takes 5–15 business days

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Credential not issued | Run cron — completion events are cron-based |
| HTTP 401 | API token wrong or missing |
| HTTP 404 | Template ID does not exist on that server |
| Observer not firing | Check `db/events.php` — eventname must be `\core\event\course_completed` |
| Plugin not detected | Check folder is at `/moodle/local/certifyme/` |

---

## Quick Commands

| Task | Command |
|---|---|
| Start Moodle | `docker run -d -p 8080:8080 -e MOODLE_USERNAME=admin -e MOODLE_PASSWORD=Admin1234! --name moodle bitnami/moodle:latest` |
| Copy plugin | `docker cp Moodle_Integration moodle:/bitnami/moodle/local/certifyme` |
| Run cron | `docker exec moodle php /bitnami/moodle/admin/cli/cron.php` |
| Watch logs | `docker exec moodle tail -f /opt/bitnami/apache/logs/error_log` |
| Plugin settings | http://localhost:8080/admin/settings.php?section=local_certifyme |

---

*CertifyMe — Moodle Plugin v2.0.0 — May 2026*
