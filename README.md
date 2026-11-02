# NSBM SportsHub — PHP and MySQL version

Sports club management system for Topic 3 of the NSBM Web and Mobile Application Development project.

## Requirements

- XAMPP with PHP 8.x, Apache and MySQL/MariaDB
- A web browser
- An internet connection for the existing Bootstrap and Google Fonts links

The project uses HTML, CSS, JavaScript, PHP and MySQL. Bootstrap is retained for the existing responsive design. No container, Node.js build, Composer installation or environment-variable configuration is needed.

## Start on your PC

1. Back up your existing `htdocs/sportshub3` folder and export your existing database using phpMyAdmin.
2. Extract the supplied `sportshub3` folder into `C:/xampp/htdocs/`.
3. Open XAMPP and start Apache and MySQL.
4. For a NEW demo database only, open `http://localhost/phpmyadmin` and import `database/sportshub.sql`.
5. Open `http://localhost/sportshub3/index.php`.

**The SQL script drops and recreates the SportsHub tables. Importing it deletes existing SportsHub records. If you already have a working `sportshub3_db`, keep that database and skip step 4.** The schema was not changed by this refactor.

Database settings are in `config/database.php`. Defaults: host `localhost`, database `sportshub3_db`, username `root`, empty password. If you change the folder name or use hosting, also edit `BASE_PATH` in `includes/init.php`.

## Demo accounts

These are the credentials documented in the supplied seed data; login must be tested on your XAMPP installation.

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@nsbm.lk | Admin@123 |
| Student | student@nsbm.lk | Student@123 |

Remove or change demo accounts before allowing real users to access the application.

## Features retained in the source

- Public club search, club details, fixtures, results and standings
- Student registration, login, membership requests and training schedule
- Match and tournament registrations and withdrawals
- Facility/equipment booking requests, cancellations and history
- Admin management of clubs, memberships, sessions, tournaments, matches and resources
- Scores, booking approvals, participation/performance reports and CSV exports

## Folder guide

| Path | Purpose |
| --- | --- |
| `config/database.php` | Database credentials and PDO connection |
| `includes/init.php` | Start the session, set the URL path, load shared files |
| `includes/functions.php` | Database queries, safe output, messages and dates |
| `includes/auth.php` | Identify the signed-in user and check their role |
| `includes/header.php`, `includes/footer.php` | Shared page layout |
| `admin/` | Admin forms and reports |
| `student/` | Student dashboard and forms |
| `assets/` | Existing CSS, simplified JavaScript and supplied favicon |
| `database/sportshub.sql` | Original schema and sample data |

See `CODE_GUIDE.md` for explanations and `VERIFICATION.md` for checks and the XAMPP test checklist.

## Live hosting for submission

The assignment requires a live URL as well as the GitHub repository. XAMPP is your local development setup.

1. Choose a host that supports PHP 8.x, PDO MySQL, sessions and MySQL/MariaDB.
2. Create a database and user in the hosting control panel.
3. For a new hosted demo database, select that database before importing the SQL and remove the `CREATE DATABASE` and `USE` statements if the host requires its own database name.
4. Update all four settings in `config/database.php` with the host's values. Keep real passwords out of public Git history.
5. Upload the project to the document root and set `BASE_PATH` to `''`. For a subfolder, use its path instead.
6. Use HTTPS and test the student/admin flows before adding the URL to your report.

The rewritten files have not been deployed by this task.
