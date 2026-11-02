# Verification and XAMPP checklist

## Checks completed in this environment

- JavaScript syntax checked with Node.js.
- JavaScript behavior checked with a small simulated DOM: valid/invalid forms, accepted/rejected confirmations and the local minimum booking date.
- Balanced PHP delimiters checked across all 31 PHP files. This is a structural check, not a PHP syntax check.
- Literal shared-file include paths checked against the packaged files.
- SQL statement literals in the feature pages compared with the uploaded export: unchanged.
- CSS, database SQL and index.html compared byte-for-byte with the uploaded export: unchanged.
- Supplied favicon included at assets/img/favicon.svg.
- No container or JavaScript build dependencies included.

## Verification limits

PHP, Apache and MySQL are unavailable in the editing environment. PHP lint, database imports, login credentials, CRUD flows and browser appearance have NOT been verified. The project is reconstructed from the supplied Gitingest text plus favicon.zip; it does not include files omitted from those uploads. Do not treat source checks as a full application test.

This refactor retains the original feature logic. It does not establish that the original booking-capacity checks, multi-sport standings calculations or tournament registration rules cover every edge case or simultaneous request.

## Run on a backed-up XAMPP copy

1. Follow README.md. Keep your existing database; only import the seed script if you intend to reset/create demo data.
2. Optionally run PHP syntax checks from PowerShell:

```powershell
Get-ChildItem C:\xampp\htdocs\sportshub3 -Filter *.php -Recurse | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }
```

3. Check homepage, clubs, fixture and result pages at desktop and mobile widths.
4. Register a new student; check invalid fields, duplicate email, incorrect password, successful login and logout.
5. Try opening an admin URL as a student: it should redirect away.
6. Request a club membership. Approve it as admin. Confirm the training schedule appears for the student.
7. Create, view, edit and delete a test club, session, tournament, match and resource. Use throwaway records because deleting parents can remove related records.
8. Register and withdraw from a tournament and match; check history and duplicate-registration behavior.
9. Request a booking; approve/reject it as admin; cancel it as the student. Test overlapping bookings and equipment quantities.
10. Enter scores and check public results and reports. Test the sport filter.
11. Download both CSV reports and check their content.
12. Confirm the navigation menu, delete confirmations and form feedback work on mobile.
13. Repeat core flows after deployment before submitting the hosted URL.
