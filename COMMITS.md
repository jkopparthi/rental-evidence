# 3-day commit plan

Add only the files listed for each commit, then commit with the message shown.
Read each file before you add it so you can explain it in an interview.

## Setup (once)

```bash
cd rental-evidence-app
git init
git branch -M main
# create an empty repo named rental-evidence-app on github.com first (no README), then:
git remote add origin https://github.com/YOUR-USERNAME/rental-evidence-app.git
```

## Day 1: foundation and accounts

```bash
git add .gitignore storage/uploads/.gitkeep docs/screenshots/.gitkeep
git commit -m "chore: initialize project structure and .gitignore"

git add database/schema.sql
git commit -m "feat(db): add MySQL schema for users, properties and photos"

git add config/config.example.php
git commit -m "chore(config): add example configuration file"

git add src/db.php src/helpers.php
git commit -m "feat(core): add PDO database connection and helper functions"

git add src/csrf.php
git commit -m "feat(security): add CSRF token generation and validation"

git add src/auth.php
git commit -m "feat(auth): add session auth and ownership checks for properties and photos"

git add src/photos.php
git commit -m "feat(photos): add upload validation, SHA-256 hashing and private storage"
## #######################################################################################
git add src/layout.php src/bootstrap.php
git commit -m "feat(ui): add shared page layout and app bootstrap"



```

## Day 2: properties and photo uploads

```bash
git add public/dashboard.php
git commit -m "feat(properties): list a user's properties with photo counts"

git add public/property_form.php public/property_delete.php
git commit -m "feat(properties): create, edit and delete properties with validation"

git add public/property.php public/upload.php
git commit -m "feat(photos): upload move-in/move-out photos grouped by room"

git add public/photo.php
git commit -m "feat(photos): stream private photos only to their owner"

git add public/photo_edit.php public/photo_delete.php
git commit -m "feat(photos): edit photo details and delete photos"

git push
```
## ##################################################################################
## Day 3: design, interactivity and docs

```bash
git add public/assets/css/style.css
git commit -m "style: add colour-coded design system, responsive layout and print styles"

git add public/assets/js/app.js
git commit -m "feat(js): add drag-and-drop previews, lightbox, AJAX delete and compare slider"

git add public/compare.php
git commit -m "feat(compare): add before/after comparison slider per room"

git add public/report.php
git commit -m "feat(report): add printable condition report with timestamps and hashes"

# take screenshots of your running app, save them in docs/screenshots/, then:
git add README.md COMMITS.md docs/screenshots
git commit -m "docs: add README with features, security notes, setup and screenshots"

git push
```

After day 3, pin the repo on your GitHub profile and add a short description and topics
(php, mysql, javascript, full-stack).
