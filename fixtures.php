<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Match fixtures';
$activePage = 'fixtures';
$sport = trim($_GET['sport'] ?? '');
$search = trim($_GET['search'] ?? '');
$conditions = ["m.status = 'scheduled'", 'm.match_datetime >= NOW()'];
$params = [];
if ($sport !== '') {
    $conditions[] = 'm.sport = ?';
    $params[] = $sport;
}
if ($search !== '') {
    $conditions[] = '(m.home_team LIKE ? OR m.away_team LIKE ? OR m.venue LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
$matches = fetch_all('SELECT m.*, t.name AS tournament_name FROM matches m LEFT JOIN tournaments t ON t.id = m.tournament_id WHERE ' . implode(' AND ', $conditions) . ' ORDER BY m.match_datetime', $params);
$sports = fetch_all('SELECT DISTINCT sport FROM matches ORDER BY sport');
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container"><span class="hero-kicker">Save the date</span>
        <h1>Upcoming fixtures
        </h1><p>Know who is playing, when to arrive and where the action happens.</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <form class="filter-bar" method="get" role="search">
            <div><label class="visually-hidden" for="search">Search fixtures</label><input class="form-control" type="search" id="search" name="search" value="<?= e($search) ?>" placeholder="Search teams or venues">
            </div>
            <div><label class="visually-hidden" for="sport">Sport</label><select class="form-select" id="sport" name="sport"><option value="">All sports</option><?php foreach ($sports as $row): ?><option value="<?= e($row['sport']) ?>" <?= $sport === $row['sport'] ? 'selected' : '' ?>><?= e($row['sport']) ?></option><?php endforeach; ?></select>
            </div><button class="btn btn-sport" type="submit">Filter fixtures</button>
        </form>
    <?php if ($matches): ?>        <div class="content-panel">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date and time
                            </th>
                            <th>Sport
                            </th>
                            <th>Fixture
                            </th>
                            <th>Tournament
                            </th>
                            <th>Venue
                            </th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($matches as $match): ?>                        <tr>
                            <td><strong><?= format_date($match['match_datetime']) ?></strong><br><small><?= format_time($match['match_datetime']) ?></small>
                            </td>
                            <td><?= e($match['sport']) ?>                            </td>
                            <td><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong>
                            </td>
                            <td><?= e($match['tournament_name'] ?? 'Friendly match') ?>                            </td>
                            <td><?= e($match['venue']) ?>                            </td>
                        </tr><?php endforeach; ?>                    </tbody>
                </table>
            </div>
        </div><?php else: ?>        <div class="empty-state">
            <h2>No matching fixtures
            </h2><p>Try another filter or check back after the next schedule update.</p><a class="btn btn-sport" href="<?= e(url('fixtures.php')) ?>">Clear filters</a>
        </div><?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
