# Understanding the code

## What changed

- Replaced typed PHP function declarations with ordinary declarations.
- Replaced the cached database factory with one PDO connection and four editable database settings.
- Kept three small query functions so pages do not repeat connection and prepared-statement code.
- Replaced automatic URL path detection with an explicit `BASE_PATH` setting.
- Replaced arrow-function ranking expressions with named functions using `if` statements.
- Replaced the spread/array-map maximum calculation with a loop.
- Rewrote JavaScript callbacks and iteration using ordinary functions and `for` loops.
- Expanded dense PHP blocks and split structural HTML onto separate lines.
- Corrected the database name and removed outdated container/environment setup references from the instructions.
- Added the uploaded favicon. The supplied archive contained only `favicon.svg`.

The original CSS and inline SVG artwork were retained to preserve the design. CSS variables, grid, flexbox, responsive sizing and modern colour notation remain part of that design. These are CSS features, not additional languages. The database schema, seed data and feature queries are retained. This is a readability refactor, not a change to the assignment's feature scope.

## How a page works

1. `require_once` loads `includes/init.php`.
2. The session remembers the user's login across requests.
3. The database connection and shared functions are loaded.
4. A protected page calls `require_admin()` or `require_student()`.
5. A GET request reads records to display. A POST request validates a submitted form before changing records.
6. Header and footer includes keep the same layout across pages.
7. After a successful POST, a redirect returns to a normal page and displays a one-time message.

## PDO and the three query helpers

PDO is built into PHP; it is not another language or a framework.

```php
$club = fetch_one('SELECT * FROM clubs WHERE id = ?', [$id]);
$clubs = fetch_all('SELECT * FROM clubs ORDER BY name');
run_query('UPDATE clubs SET name = ? WHERE id = ?', [$name, $id]);
```

`fetch_one` returns one row or `false`; `fetch_all` returns a list of rows. `run_query` returns the statement, which some pages use to check the affected row count. Each helper prepares the SQL and passes submitted values separately to `execute`. Do not concatenate student input into SQL.

## Short syntax used in the pages

- `<?= e($name) ?>` prints a value safely inside HTML. It means `<?php echo e($name); ?>`.
- `$_POST['name'] ?? ''` uses the submitted value when present, otherwise an empty string.
- `$condition ? $yes : $no` chooses one of two values.
- `foreach ($rows as $row)` loops over database rows.
- `if (...): ... endif;` and `foreach (...): ... endforeach;` are PHP's template syntax for mixing PHP with HTML.
- `__DIR__` means the directory containing the current PHP file.
- `global $connection` makes the shared PDO connection available inside a query function.

## Security code to understand and keep

- `password_hash` stores a password hash; `password_verify` checks a password.
- `e()` uses `htmlspecialchars` to stop submitted text being treated as HTML.
- `csrf_field()` adds a random session token to a form; `verify_csrf()` checks it before a change.
- `require_admin()` protects the server-side action, not just the visible admin menu.
- Session cookie settings and session ID regeneration help protect login sessions.
- Server-side validation remains necessary even when the browser validates fields.

## Group review

Each member should read, test and explain the parts they actually work on. Keep genuine Git contributions and follow your lecturer's rules on assistance and attribution. Use the included contribution guide as an organisational example, adjusting it to your actual group size and responsibilities.
