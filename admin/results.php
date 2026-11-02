<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $homeScore = $_POST['home_score'] ?? '';
    $awayScore = $_POST['away_score'] ?? '';
    if (!fetch_one('SELECT id FROM matches WHERE id=?', [$id]) || !in_array($status, ['scheduled', 'completed', 'cancelled'], true)) {
        flash('danger', 'Invalid result update.');
    } elseif ($status === 'completed' && (!is_numeric($homeScore) || !is_numeric($awayScore) || (int) $homeScore < 0 || (int) $awayScore < 0)) {
        flash('danger', 'Enter valid non-negative scores before completing a match.');
    } else {
        $home = $status === 'completed' ? (int) $homeScore : null;
        $away = $status === 'completed' ? (int) $awayScore : null;
        run_query('UPDATE matches SET status=?, home_score=?, away_score=? WHERE id=?', [$status, $home, $away, $id]);
        flash('success', 'Match result updated.');
    }
    redirect('admin/results.php');
}
$pageTitle = 'Record match results';
$activePage = 'dashboard';
$adminSection = 'results';
$matches = fetch_all('SELECT * FROM matches ORDER BY FIELD(status, "scheduled", "completed", "cancelled"), match_datetime DESC');
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Result management</span>
    <h1>Match results
    </h1><p>Record final scores or return a match to the scheduled list.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fixture
                        </th>
                        <th>Date
                        </th>
                        <th>Home score
                        </th>
                        <th>Away score
                        </th>
                        <th>Status
                        </th>
                        <th>Action
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($matches as $match): ?>                    <tr>
                        <td>
<form id="result-<?= $match['id'] ?>" method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="id" value="<?= $match['id'] ?>">
                        </form><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong><br><small><?= e($match['sport']) ?> · <?= e($match['venue']) ?></small>
                    </td>
                    <td><?= format_date($match['match_datetime']) ?><br><small><?= format_time($match['match_datetime']) ?></small>
                    </td>
                    <td><label class="visually-hidden" for="home-<?= $match['id'] ?>">Home score</label><input class="form-control" type="number" id="home-<?= $match['id'] ?>" name="home_score" min="0" max="999" value="<?= e((string) $match['home_score']) ?>" form="result-<?= $match['id'] ?>">
                    </td>
                    <td><label class="visually-hidden" for="away-<?= $match['id'] ?>">Away score</label><input class="form-control" type="number" id="away-<?= $match['id'] ?>" name="away_score" min="0" max="999" value="<?= e((string) $match['away_score']) ?>" form="result-<?= $match['id'] ?>">
                    </td>
                    <td><label class="visually-hidden" for="status-<?= $match['id'] ?>">Match status</label><select class="form-select" id="status-<?= $match['id'] ?>" name="status" form="result-<?= $match['id'] ?>"><?php foreach (['scheduled', 'completed', 'cancelled'] as $option): ?><option value="<?= $option ?>" <?= $match['status'] === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach; ?></select>
                    </td>
                    <td><button class="btn btn-sm btn-sport" type="submit" form="result-<?= $match['id'] ?>">Save</button>
                    </td>
                </tr><?php endforeach; ?>            </tbody>
        </table>
    </div>
</section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
