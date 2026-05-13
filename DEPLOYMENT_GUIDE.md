# CertifyMe Moodle Plugin — Complete Deployment Guide

**Plugin:** `local_certifyme` | **Version:** 2.0.0 | **Date:** May 2026

---

## Table of Contents

1. [Prerequisites](#1-prerequisites)
2. [Phase 1 — API Test (No Moodle)](#2-phase-1--api-test-no-moodle)
3. [Phase 2 — Local Moodle Setup](#3-phase-2--local-moodle-setup)
4. [Phase 3 — Install Plugin in Moodle](#4-phase-3--install-plugin-in-moodle)
5. [Phase 4 — Full End-to-End Test](#5-phase-4--full-end-to-end-test)
6. [Phase 5 — Production Readiness Checklist](#6-phase-5--production-readiness-checklist)
7. [Phase 6 — GitHub Setup](#7-phase-6--github-setup)
8. [Phase 7 — Submit to Moodle Plugins Directory](#8-phase-7--submit-to-moodle-plugins-directory)
9. [Troubleshooting](#9-troubleshooting)

---

## 1. Prerequisites

Before starting, have the following ready:

| Item | Where to get it |
|---|---|
| CertifyMe API Token | Your CertifyMe dashboard (APAC / EU2 / US1 / Butterfly) |
| CertifyMe Template ID | Your CertifyMe dashboard → Templates |
| Docker Desktop | https://docker.com |
| Git | Already installed on most systems |
| moodle.org account | https://moodle.org (free) |
| GitHub account | https://github.com/signup (free) |

---

## 2. Phase 1 — API Test (No Moodle)

**Goal:** Confirm your API token, template ID, and server URL work before touching Moodle.

### Step 1 — Test APAC Server

Open a terminal and run:

```bash
curl -X POST https://apac.platform.certifyme.dev/api/v2/credential \
  -H "Authorization: YOUR_API_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "accept: application/json" \
  -d '{
    "name": "Test Student",
    "email": "your@email.com",
    "template_ID": "YOUR_TEMPLATE_ID_HERE",
    "text": "",
    "verify_mode": "None",
    "Custom.CourseName": "Test Course",
    "Custom.eventdate": "13 May 2026"
  }'
```

**Expected result:** HTTP 200 response + credential appears in your CertifyMe dashboard.

### Step 2 — Test Other Servers (if applicable)

Repeat the curl command for each server you plan to support, replacing the URL:

```
EU2:       https://eu2.certifyme.org/api/v2/credential
US1:       https://us1.certifyme.org/api/v2/credential
Butterfly: https://butterfly.certifyme.org/api/v2/credential
```

### Step 3 — Confirm Custom Field Names

> **Critical:** Custom field names are case-sensitive and must exactly match what is configured in your CertifyMe template.

Log in to your CertifyMe dashboard and check your template's custom field names. If they differ from `Custom.CourseName` or `Custom.eventdate`, update [classes/observer.php](classes/observer.php) before proceeding.

**Pass this phase when:** All servers you use return HTTP 200 and credentials appear in the dashboard.

---

## 3. Phase 2 — Local Moodle Setup

**Goal:** Get a local Moodle instance running to install and test the plugin.

### Option A — Bitnami Docker (Recommended — Simplest)

#### Step 1 — Install Docker Desktop

Download and install from https://docker.com. Start Docker Desktop and wait until it shows "Docker is running".

#### Step 2 — Run Moodle Container

```bash
docker run -d \
  -p 8080:8080 \
  -p 8443:8443 \
  -e MOODLE_USERNAME=admin \
  -e MOODLE_PASSWORD=Admin1234! \
  -e MOODLE_EMAIL=admin@example.com \
  -e MOODLE_SITE_NAME="Test Moodle" \
  --name moodle \
  bitnami/moodle:latest
```

#### Step 3 — Wait for Moodle to Boot

```bash
# Watch the logs — wait until you see "Starting Apache"
docker logs -f moodle
```

This takes 2–5 minutes on first run.

#### Step 4 — Open Moodle

Go to http://localhost:8080 in your browser.
Log in with username `admin` and password `Admin1234!`.

---

### Option B — Moodle Docker (Official — More Control)

#### Step 1 — Clone Moodle Source

```bash
git clone --depth=1 --branch MOODLE_404_STABLE https://github.com/moodle/moodle.git /tmp/moodle
```

#### Step 2 — Clone Moodle Docker

```bash
git clone https://github.com/moodlehq/moodle-docker.git /tmp/moodle-docker
cd /tmp/moodle-docker
cp config.docker-template.php config.php
```

#### Step 3 — Start Containers

```bash
export MOODLE_DOCKER_WWWROOT=/tmp/moodle
export MOODLE_DOCKER_DB=pgsql
bin/moodle-docker-compose up -d
```

#### Step 4 — Run Moodle Install

```bash
bin/moodle-docker-compose exec webserver php admin/cli/install_database.php \
  --agree-license \
  --adminpass=Admin1234! \
  --adminemail=admin@example.com \
  --fullname="Test Moodle" \
  --shortname=test
```

Open http://localhost:8000 — log in with `admin` / `Admin1234!`.

---

## 4. Phase 3 — Install Plugin in Moodle

### Step 1 — Copy Plugin Files Into Moodle

**If using Bitnami Docker:**

```bash
# Copy plugin folder into the running container
docker cp /home/harshit/Desktop/certifyme-codes/Moodle_Integration \
  moodle:/bitnami/moodle/local/certifyme
```

**If using Moodle Docker (Option B):**

```bash
cp -r /home/harshit/Desktop/certifyme-codes/Moodle_Integration \
  /tmp/moodle/local/certifyme
```

**If testing on a real server:**

```bash
scp -r /home/harshit/Desktop/certifyme-codes/Moodle_Integration \
  user@yourserver.com:/path/to/moodle/local/certifyme
```

### Step 2 — Enable Debug Mode in Moodle

This is essential for seeing errors during testing. In the Moodle admin panel:

1. Go to **Site Administration → Development → Debugging**
2. Set **Debug messages** to `DEVELOPER: extra Moodle debug messages for developers`
3. Set **Display debug messages** to `Yes`
4. Click **Save changes**

### Step 3 — Trigger Plugin Installation

1. Go to **Site Administration → Notifications**
2. Moodle will detect the new plugin and show an upgrade page
3. Click **Upgrade Moodle database now**
4. Wait for "Success" — click **Continue**

### Step 4 — Configure the Plugin

1. Go to **Site Administration → Plugins → Local Plugins → CertifyMe**
2. Select your **CertifyMe Server** from the dropdown
3. Enter your **API Token** (from your CertifyMe dashboard)
4. Enter your **Template ID** (from your CertifyMe dashboard → Templates)
5. Click **Save changes**

**Verify:** The settings page should show all four servers in the dropdown (APAC, EU2, US1, Butterfly).

---

## 5. Phase 4 — Full End-to-End Test

### Step 1 — Create a Test Course

1. Go to **Site Administration → Courses → Manage courses and categories**
2. Click **Create new course**
3. Fill in:
   - Full name: `CertifyMe Test Course`
   - Short name: `CMTEST`
4. Under **Completion tracking**, set **Enable completion tracking** to `Yes`
5. Click **Save and display**

### Step 2 — Set Completion Criteria

1. Inside the course, go to **Course administration → Course completion**
2. Under **General**, check **Manual self completion**
3. Click **Save changes**

### Step 3 — Create a Test Student

1. Go to **Site Administration → Users → Add a new user**
2. Fill in:
   - First name: `Test`
   - Last name: `Student`
   - Email: use a real email you can check (credential will be sent here)
   - Username: `teststudent`
   - Password: `Student1234!`
3. Click **Create user**

### Step 4 — Enrol the Student

1. Go back to the test course
2. Go to **Course administration → Users → Enrolment methods**
3. Click the enrol icon next to **Manual enrolments**
4. Search for `teststudent` and enrol them

### Step 5 — Trigger Course Completion

**Option A — Mark complete as admin:**

1. Go to **Course administration → Users → Enrolled users**
2. Find `teststudent` — click the completion checkbox in their row
3. Confirm the mark

**Option B — Let the student self-complete:**

1. Log in as `teststudent`
2. Go to the test course
3. Click **Mark as done** in the Self completion block

### Step 6 — Run the Completion Cron (Important)

Moodle's course completion event fires via cron, not instantly. Run:

```bash
# Bitnami Docker:
docker exec moodle php /bitnami/moodle/admin/cli/cron.php

# Moodle Docker (Option B):
bin/moodle-docker-compose exec webserver php admin/cli/cron.php

# Direct server:
php /path/to/moodle/admin/cli/cron.php
```

### Step 7 — Verify Results

Check these in order:

1. **Moodle Logs:** Site Administration → Reports → Logs
   - Filter by user: `teststudent`
   - Look for any CertifyMe debug messages

2. **CertifyMe Dashboard:** Log in to your server's dashboard
   - The credential should appear with the student name, email, and course name

3. **Student Email:** Check the inbox of the test student's email
   - CertifyMe sends the credential by email automatically

---

### Multi-Server Test

Repeat Steps 1–7 for each server:

1. Go to **Site Administration → Plugins → Local Plugins → CertifyMe**
2. Switch the server dropdown to **EU2** (or US1, Butterfly) — save
3. Trigger another course completion (use a different student or different course)
4. Run cron again
5. Verify the credential appears on the **EU2** dashboard, not APAC

**Pass this phase when:** Credential appears on the correct server dashboard and the student receives the email.

---

## 6. Phase 5 — Production Readiness Checklist

Go through every item before submitting to Moodle directory.

### Code Checks

- [ ] `version.php` — component is `local_certifyme`, not `mod_certifyme`
- [ ] `version.php` — requires Moodle 4.0 (`2022041900`) or higher
- [ ] `db/events.php` — listens to `\core\event\course_completed` only
- [ ] `classes/observer.php` — all four server URLs are correct
- [ ] `classes/privacy/provider.php` — exists and implements `metadata\provider`
- [ ] `lang/en/local_certifyme.php` — all strings present including `server`, `server_desc`
- [ ] Every PHP file has the GPL v3 license header at the top
- [ ] No hardcoded API tokens or template IDs anywhere in the code
- [ ] No `var_dump`, `print_r`, or `die()` debug statements left in code

### GPL v3 Header — Every PHP File Must Have This

```php
<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// @package    local_certifyme
// @copyright  2026 CertifyMe (https://www.certifyme.online)
// @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
```

### Functional Checks

- [ ] Plugin installs on a clean Moodle 4.x with zero errors
- [ ] Settings page shows the server dropdown with all four options
- [ ] Server selection saves correctly and persists after page reload
- [ ] Changing server routes API calls to the new server (verify via logs)
- [ ] Course completion triggers the observer (check Moodle debug logs)
- [ ] API returns HTTP 200 for each server
- [ ] Credential appears in the correct CertifyMe dashboard
- [ ] Student receives credential email
- [ ] Tested on MySQL **and** PostgreSQL (Moodle Docker supports both)
- [ ] Tested on APAC server
- [ ] Tested on at least one other server (EU2, US1, or Butterfly)
- [ ] No PHP errors with debug mode fully enabled

### Switch to PostgreSQL Test

```bash
# Stop current container and start with PostgreSQL
docker stop moodle && docker rm moodle
docker run -d \
  -p 8080:8080 \
  -e MOODLE_DATABASE_TYPE=pgsql \
  -e MOODLE_USERNAME=admin \
  -e MOODLE_PASSWORD=Admin1234! \
  --name moodle-pg \
  bitnami/moodle:latest
```

Reinstall plugin, repeat the end-to-end test. Both MySQL and PostgreSQL must pass.

### Screenshots to Take (Required for Submission)

Take clear browser screenshots of:

1. The plugin settings page showing the server dropdown (all four servers visible)
2. The settings page filled in with APAC selected
3. A credential appearing in the CertifyMe dashboard after course completion
4. The Moodle course completion page (proof the trigger works)

Save these — you will upload them during Moodle directory submission.

---

## 7. Phase 6 — GitHub Setup

### Step 1 — Create the Repository

1. Go to https://github.com/new
2. Set **Repository name** to exactly: `moodle-local_certifyme`
   > The name must be exactly this — Moodle directory requires it
3. Set **Visibility** to **Public**
4. Check **Add a README file** — No (we already have one)
5. Set **License** to **GNU General Public License v3.0**
6. Enable **Issues** (Settings → Features → Issues — must be checked)
7. Click **Create repository**

### Step 2 — Push the Plugin Code

```bash
cd /home/harshit/Desktop/certifyme-codes/Moodle_Integration

# Set remote (replace with your actual GitHub username/org)
git remote set-url origin https://github.com/certifyme/moodle-local_certifyme.git

# Stage all files
git add version.php settings.php README.md LICENCE \
        db/events.php \
        classes/observer.php \
        classes/privacy/provider.php \
        lang/en/local_certifyme.php

# Commit
git commit -m "v2.0.0 — multi-server local plugin (APAC, EU2, US1, Butterfly)"

# Tag the release
git tag v2.0.0

# Push with tags
git push origin main --tags
```

### Step 3 — Verify Repository Structure on GitHub

After pushing, your GitHub repo must look exactly like this:

```
moodle-local_certifyme/
├── version.php
├── settings.php
├── README.md
├── LICENCE
├── db/
│   └── events.php
├── classes/
│   ├── observer.php
│   └── privacy/
│       └── provider.php
└── lang/
    └── en/
        └── local_certifyme.php
```

### Step 4 — Create a Release on GitHub

1. Go to your repo on GitHub
2. Click **Releases → Create a new release**
3. Select tag: `v2.0.0`
4. Title: `v2.0.0 — Multi-Server Support`
5. Description:
   ```
   - Added multi-server support: APAC, EU2, US1, Butterfly
   - Admin selects server from settings dropdown
   - Plugin routes API calls to the correct server automatically
   - Requires Moodle 4.0+
   ```
6. Click **Publish release**

### Step 5 — Create a ZIP of the Plugin

Moodle requires a ZIP where the folder inside is named exactly `certifyme`:

```bash
cd /home/harshit/Desktop/certifyme-codes

# Create zip with correct internal folder name
zip -r moodle-local_certifyme-v2.0.0.zip Moodle_Integration \
  --exclude "*.git*" \
  --exclude "*.DS_Store"

# Verify the zip contents
unzip -l moodle-local_certifyme-v2.0.0.zip | head -30
```

> **Important:** When Moodle extracts the ZIP, the root folder must be named `certifyme` (matching the plugin component `local_certifyme`). If your folder is named differently, rename it before zipping:
> ```bash
> cp -r Moodle_Integration certifyme
> zip -r moodle-local_certifyme-v2.0.0.zip certifyme --exclude "*.git*"
> rm -rf certifyme
> ```

---

## 8. Phase 7 — Submit to Moodle Plugins Directory

### Step 1 — Create a Moodle.org Account

1. Go to https://moodle.org
2. Click **Log in → Create new account**
3. Fill in: username, email, first name, last name
4. Verify your email address (check inbox for confirmation link)
5. Log in at https://moodle.org/login

### Step 2 — Go to the Plugin Submission Page

URL: https://moodle.org/plugins/registerplugin.php

### Step 3 — Fill in the Submission Form

| Field | What to Enter |
|---|---|
| **Plugin name** | CertifyMe |
| **Plugin type** | Local (local) |
| **Short description** | Automatically issues CertifyMe digital credentials when a student completes a Moodle course. Supports APAC, EU2, US1 and Butterfly servers. |
| **Supported Moodle versions** | 4.0, 4.1, 4.2, 4.3, 4.4, 4.5 |
| **GitHub repository URL** | https://github.com/certifyme/moodle-local_certifyme |
| **Bug tracker URL** | https://github.com/certifyme/moodle-local_certifyme/issues |
| **License** | GNU GPL v3 or later |

### Step 4 — Write the Full Description

Use this for the submission form's description field:

```
CertifyMe automatically issues digital credentials (certificates and badges) 
when a student completes a course in Moodle.

How it works:
- Admin configures the plugin with their CertifyMe server, API token and template ID
- When a student completes a course, Moodle fires the course_completed event
- The plugin sends the student name, email and course name to CertifyMe via REST API
- The student receives their digital credential by email automatically

Multi-Server Support (v2.0):
Supports all four CertifyMe servers — APAC, EU2, US1 and Butterfly.
Admin selects the correct server from a dropdown in plugin settings.

Requirements:
- Moodle 4.0 or higher
- PHP 7.4 or higher
- A CertifyMe account with API Token and Template ID

No manual credential issuance required. Works fully automatically after setup.
```

### Step 5 — Upload the ZIP

Upload the ZIP file you created in Phase 6 Step 5.

### Step 6 — Upload Screenshots

Upload the screenshots you took in Phase 5:
- Settings page with server dropdown
- CertifyMe dashboard showing issued credential

### Step 7 — Submit

Click **Submit** and note down your submission reference number.

---

### After Submission

| Timeframe | What Happens |
|---|---|
| Day 1–3 | Automated checks run (code structure, license headers, naming) |
| Day 3–15 | Manual review by Moodle community reviewer |
| During review | You may get email asking for fixes — respond promptly |
| After approval | Plugin goes live and is searchable on moodle.org/plugins |

**Common rejection reasons to avoid:**
- Missing GPL v3 header in any PHP file
- `privacy/provider.php` missing or incomplete
- Repository not public or Issues not enabled
- Plugin component name mismatch (`local_certifyme` must match everywhere)
- PHP warnings/errors in debug mode

### Updating the Plugin After Approval

For future updates:

```bash
# 1. Bump version in version.php (e.g., 2026060100)
# 2. Commit and push
git add .
git commit -m "v2.1.0 — describe what changed"
git tag v2.1.0
git push origin main --tags

# 3. Create new ZIP
zip -r moodle-local_certifyme-v2.1.0.zip certifyme --exclude "*.git*"

# 4. Go to your plugin listing on moodle.org
# 5. Click "Add new version" and upload the new ZIP
```

---

## 9. Troubleshooting

### Credential not being issued

| Symptom | Fix |
|---|---|
| No credential in dashboard | Run the cron: `php admin/cli/cron.php` — course completion is cron-based |
| HTTP 401 in debug log | API token is wrong or missing — check settings |
| HTTP 404 in debug log | Template ID does not exist on that server |
| HTTP 500 in debug log | Contact support@certifyme.cc with the response body |
| Observer not triggered | Check `db/events.php` — eventname must be `\core\event\course_completed` |

### Plugin not showing in Moodle

| Symptom | Fix |
|---|---|
| Not appearing after copy | Check folder is at `/moodle/local/certifyme/` — not `/local/local_certifyme/` |
| Error on upgrade page | Check PHP error log — likely a syntax error in one of the PHP files |
| Settings page empty | Check `settings.php` — `$hassiteconfig` block must wrap all settings |

### Checking Moodle Debug Logs

```bash
# Real-time log watching (Bitnami Docker)
docker exec moodle tail -f /opt/bitnami/apache/logs/error_log

# Run cron with verbose output
docker exec moodle php /bitnami/moodle/admin/cli/cron.php --verbose
```

### Verify Plugin is Registered Correctly

In Moodle admin, go to:
**Site Administration → Plugins → Plugin Overview**

Search for `certifyme` — you should see `local_certifyme` listed as a Local plugin.

---

## Quick Reference

| Task | Command / URL |
|---|---|
| Start Moodle Docker | `docker run -d -p 8080:8080 -e MOODLE_USERNAME=admin -e MOODLE_PASSWORD=Admin1234! --name moodle bitnami/moodle:latest` |
| Copy plugin to Docker | `docker cp Moodle_Integration moodle:/bitnami/moodle/local/certifyme` |
| Run cron | `docker exec moodle php /bitnami/moodle/admin/cli/cron.php` |
| Watch logs | `docker exec moodle tail -f /opt/bitnami/apache/logs/error_log` |
| Moodle admin panel | http://localhost:8080/admin |
| Plugin settings | http://localhost:8080/admin/settings.php?section=local_certifyme |
| Submit plugin | https://moodle.org/plugins/registerplugin.php |
| CertifyMe support | support@certifyme.cc |

---

*CertifyMe Moodle Plugin — Deployment Guide v2.0.0 — May 2026*
