<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$errors = [];
$values = ['id' => '', 'tournament_id' => '', 'sport' => '', 'home_team' => '', 'away_team' => '', 'match_datetime' => '', 'venue' => '', 'status' => 'scheduled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        run_query('DELETE FROM matches WHERE id=?', [$id]);
        flash('success', 'Match deleted.');
        redirect('admin/matches.php');
    }
    if ($action === 'save') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $values['id'] = (string) $id;
        if ($values['tournament_id'] && !fetch_one('SELECT id FROM tournaments WHERE id=?', [(int) $values['tournament_id']])) {
            $errors[] = 'Choose a valid tournament.';
        }
        if ($values['sport'] === '') {
            $errors[] = 'Sport is required.';
        }
        if (strlen($values['home_team']) < 2 || strlen($values['away_team']) < 2) {
            $errors[] = 'Both team names are required.';
        }
        if (strcasecmp($values['home_team'], $values['away_team']) === 0) {
            $errors[] = 'Home and away teams must be different.';
        }
        if (!strtotime($values['match_datetime'])) {
            $errors[] = 'Choose a valid match date and time.';
        }
        if ($values['venue'] === '') {
            $errors[] = 'Venue is required.';
        }
        if (!in_array($values['status'], ['scheduled', 'completed', 'cancelled'], true)) {
            $errors[] = 'Choose a valid status.';
        }
        if ($values['status'] === 'completed') {
            $existing = $id ? fetch_one('SELECT home_score, away_score FROM matches WHERE id=?', [$id]) : false;
            if (!$existing || $existing['home_score'] === null || $existing['away_score'] === null) {
                $errors[] = 'Add scores on the Results page before marking a match completed.';
            }
        }
        if (!$errors) {
            $dateTime = str_replace('T', ' ', $values['match_datetime']);
            $tournamentId = $values['tournament_id'] ? (int) $values['tournament_id'] : null;
            $data = [$tournamentId, $values['sport'], $values['home_team'], $values['away_team'], $dateTime, $values['venue'], $values['status']];
            if ($id) {
                $data[] = $id;
                run_query('UPDATE matches SET tournament_id=?, sport=?, home_team=?, away_team=?, match_datetime=?, venue=?, status=? WHERE id=?', $data);
                flash('success', 'Match updated.');
            }
            else {
                run_query('INSERT INTO matches (tournament_id, sport, home_team, away_team, match_datetime, venue, status) VALUES (?, ?, ?, ?, ?, ?, ?)', $data);
                flash('success', 'Match scheduled.');
            }
            redirect('admin/matches.php');
        }
    }
}
elseif (isset($_GET['edit'])) {
    $match = fetch_one('SELECT * FROM matches WHERE id=?', [(int) $_GET['edit']]);
    if ($match) {
        $values = array_merge($values, $match);
        $values['match_datetime'] = date('Y-m-d\TH:i', strtotime($match['match_datetime']));
    }
}
$pageTitle = 'Manage matches';
$activePage = 'dashboard';
$adminSection = 'matches';
$tournaments = fetch_all('SELECT id, name FROM tournaments ORDER BY start_date DESC');
$matches = fetch_all('SELECT m.*, t.name AS tournament_name FROM matches m LEFT JOIN tournaments t ON t.id=m.tournament_id ORDER BY m.match_datetime DESC');
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Fixture management</span>
    <h1>Matches
    </h1><p>Schedule fixtures, assign tournaments and keep match details up to date.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2><?= $values['id'] ? 'Edit match' : 'Schedule match' ?>            </h2><?php if ($values['id']): ?><a class="link-arrow" href="<?= e(url('admin/matches.php')) ?>">Cancel editing</a><?php endif; ?>        </div><?php if ($errors): ?>        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $error): ?>                <li><?= e($error) ?>                </li><?php endforeach; ?>            </ul>
        </div><?php endif; ?>        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($values['id']) ?>">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="tournament_id">Tournament (optional)</label><select class="form-select" id="tournament_id" name="tournament_id"><option value="">Friendly / standalone match</option><?php foreach ($tournaments as $tournament): ?><option value="<?= $tournament['id'] ?>" <?= $values['tournament_id'] == $tournament['id'] ? 'selected' : '' ?>><?= e($tournament['name']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-4"><label class="form-label" for="sport">Sport</label><input class="form-control" id="sport" name="sport" value="<?= e($values['sport']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="venue">Venue</label><input class="form-control" id="venue" name="venue" value="<?= e($values['venue']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="home_team">Home team</label><input class="form-control" id="home_team" name="home_team" value="<?= e($values['home_team']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="away_team">Away team</label><input class="form-control" id="away_team" name="away_team" value="<?= e($values['away_team']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="match_datetime">Date and time</label><input class="form-control" type="datetime-local" id="match_datetime" name="match_datetime" value="<?= e($values['match_datetime']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><?php foreach (['scheduled', 'completed', 'cancelled'] as $option): ?><option value="<?= $option ?>" <?= $values['status'] === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach; ?></select>
                    <div class="form-text">Use Results to enter scores.
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-sport w-100" type="submit"><?= $values['id'] ? 'Save changes' : 'Schedule match' ?></button>
                </div>
            </div>
        </form>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">Match list
        </h2><?php if ($matches): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date
                        </th>
                        <th>Match
                        </th>
                        <th>Tournament
                        </th>
                        <th>Status
                        </th>
                        <th>Score
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($matches as $match): ?>                    <tr>
                        <td><?= format_date($match['match_datetime']) ?><br><small><?= format_time($match['match_datetime']) ?></small>
                        </td>
                        <td><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong><br><small><?= e($match['sport']) ?> · <?= e($match['venue']) ?></small>
                        </td>
                        <td><?= e($match['tournament_name'] ?? 'Friendly') ?>                        </td>
                        <td><?= status_badge($match['status']) ?>                        </td>
                        <td class="score"><?= $match['home_score'] !== null ? $match['home_score'] . '–' . $match['away_score'] : '—' ?>                        </td>
                        <td>
                            <div class="table-actions"><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/matches.php?edit=' . $match['id'])) ?>">Edit</a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $match['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this match and its student registrations?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No matches yet.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
