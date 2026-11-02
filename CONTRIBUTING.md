# Six-member GitHub contribution guide

Use your **existing** GitHub repository. Replace the sample branch names and member labels with your actual names. Each member should understand, review and test the files they commit. Do not create fake commits or fake author identities—the lecturer can use commit history to assess contribution.

## 1. Team leader: prepare the base

Clone your existing repository and copy the SportsHub source into it. Do not copy these files into a second nested `app` folder; `index.php` should be at the repository root.

```bash
git clone YOUR_REPOSITORY_URL
cd YOUR_REPOSITORY_FOLDER
# Copy the SportsHub files here, then:
git add README.md CONTRIBUTING.md database config includes
git commit -m "Set up SportsHub database and shared application structure"
git push origin main
```

If your default branch is called `master`, use `master` instead of `main`.

## 2. Suggested ownership split

| Member | Contribution area | Main files |
| --- | --- | --- |
| Member 1 | Public UI and visual design | `index.php`, `assets/`, `includes/header.php`, `includes/footer.php` |
| Member 2 | Authentication and clubs | `login.php`, `register.php`, `logout.php`, `clubs.php`, `club.php` |
| Member 3 | Fixtures, results and student schedule | `fixtures.php`, `results.php`, `student/dashboard.php`, `student/calendar.php` |
| Member 4 | Student registrations, bookings and history | `student/registrations.php`, `student/bookings.php`, `student/history.php` |
| Member 5 | Admin clubs, memberships and training | `admin/clubs.php`, `admin/memberships.php`, `admin/sessions.php`, `admin/index.php` |
| Member 6 | Admin events, resources and reports | `admin/tournaments.php`, `admin/matches.php`, `admin/results.php`, `admin/facilities.php`, `admin/bookings.php`, `admin/reports.php` |

Shared files should be changed through discussion, because every member depends on them.

## 3. Each member: create a feature branch

Example for Member 1:

```bash
git checkout main
git pull origin main
git checkout -b member-1-public-ui
# Review/test the assigned files and make any needed improvements.
git add index.php assets includes/header.php includes/footer.php
git commit -m "Build public SportsHub interface and responsive styling"
git push -u origin member-1-public-ui
```

Then open a Pull Request on GitHub, ask another member to review it, and merge it.

Other members can use the same pattern:

```bash
git checkout -b member-2-auth-clubs
# Add only the files you actually worked on.
git add login.php register.php logout.php clubs.php club.php
git commit -m "Add authentication and club membership requests"
git push -u origin member-2-auth-clubs
```

## 4. Useful Git commands

```bash
git status                 # See changed files before committing
git diff                   # Review exact changes
git log --oneline          # See commit history
git pull origin main       # Get the latest integrated work
git branch                 # List local branches
```

Configure each computer with the member's own identity before committing:

```bash
git config user.name "Your Real Name"
git config user.email "YOUR_GITHUB_EMAIL"
```

## 5. Demonstration video contribution guide

Give each member a short segment matching their ownership area:

1. Public design and responsive behaviour
2. Login, registration and club requests
3. Fixtures, results and student schedule
4. Event registration, bookings and history
5. Admin clubs, approvals and training CRUD
6. Tournaments, scores, resources and reports

Each member should explain what they built, show it working, and mention one challenge or thing they learned.
