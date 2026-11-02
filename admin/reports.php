<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$clubReport = fetch_all("SELECT c.name, c.sport, (SELECT COUNT(*) FROM memberships m WHERE m.club_id=c.id AND m.status='approved') AS approved, (SELECT COUNT(*) FROM memberships m WHERE m.club_id=c.id AND m.status='pending') AS pending, (SELECT COUNT(*) FROM training_sessions s WHERE s.club_id=c.id) AS sessions FROM clubs c ORDER BY approved DESC, c.name");
$studentReport = fetch_all("SELECT u.full_name, u.student_id, (SELECT COUNT(*) FROM memberships m WHERE m.user_id=u.id AND m.status='approved') AS clubs, (SELECT COUNT(*) FROM tournament_registrations tr WHERE tr.user_id=u.id AND tr.status='registered') AS tournaments, (SELECT COUNT(*) FROM match_registrations mr WHERE mr.user_id=u.id AND mr.status='registered') AS matches, (SELECT COUNT(*) FROM bookings b WHERE b.user_id=u.id AND b.status='approved') AS bookings FROM users u WHERE u.role='student' ORDER BY u.full_name");
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sportshub-' . ($type === 'students' ? 'student-participation' : 'club-participation') . '-' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    if ($type === 'students') {
        fputcsv($output, ['Student ID', 'Name', 'Approved clubs', 'Tournament entries', 'Match entries', 'Approved bookings', 'Total activities']);
        foreach ($studentReport as $row) fputcsv($output, [$row['student_id'], $row['full_name'], $row['clubs'], $row['tournaments'], $row['matches'], $row['bookings'], $row['clubs'] + $row['tournaments'] + $row['matches'] + $row['bookings']]);
    }
    else {
        fputcsv($output, ['Club', 'Sport', 'Approved members', 'Pending requests', 'Training sessions']);
        foreach ($clubReport as $row) fputcsv($output, [$row['name'], $row['sport'], $row['approved'], $row['pending'], $row['sessions']]);
    }
    fclose($output);
    exit;
}
$completed = fetch_all("SELECT * FROM matches WHERE status='completed' ORDER BY match_datetime");
$performance = [];
foreach ($completed as $match) {
    foreach (['home', 'away'] as $side) {
        $team = $match[$side . '_team'];
        if (!isset($performance[$team])) $performance[$team] = ['team' => $team, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'for' => 0, 'against' => 0];
        $otherSide = $side === 'home' ? 'away' : 'home';
        $own = (int) $match[$side . '_score'];
        $other = (int) $match[$otherSide . '_score'];
        $performance[$team]['played']++;
        $performance[$team]['for'] += $own;
        $performance[$team]['against'] += $other;
        if ($own > $other) $performance[$team]['won']++;
        elseif ($own === $other) $performance[$team]['drawn']++;
        else $performance[$team]['lost']++;
    }
}
function compare_performance($first, $second)
{
    if ($first['won'] != $second['won']) {
        return $second['won'] - $first['won'];
    }
    if ($first['played'] != $second['played']) {
        return $first['played'] - $second['played'];
    }
    return strcmp($first['team'], $second['team']);
}
usort($performance, 'compare_performance');
$totalApproved = array_sum(array_column($clubReport, 'approved'));
$totalActivities = 0;
foreach ($studentReport as $row) $totalActivities += $row['clubs'] + $row['tournaments'] + $row['matches'] + $row['bookings'];
$maxMembers = 1;
foreach ($clubReport as $club) {
    if ($club['approved'] > $maxMembers) {
        $maxMembers = (int) $club['approved'];
    }
}
$pageTitle = 'Participation and performance reports';
$activePage = 'dashboard';
$adminSection = 'reports';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Sports office reports</span>
    <h1>Participation and performance
    </h1><p>Database-generated summaries for club planning and semester reporting.</p>
</div>
<div class="container dashboard-content">
    <div class="metric-grid">
        <div class="metric-card"><strong><?= count($studentReport) ?></strong><span>Student accounts</span>
        </div>
        <div class="metric-card"><strong><?= $totalApproved ?></strong><span>Approved club memberships</span>
        </div>
        <div class="metric-card"><strong><?= $totalActivities ?></strong><span>Recorded participation activities</span>
        </div>
        <div class="metric-card"><strong><?= count($completed) ?></strong><span>Completed matches</span>
        </div>
    </div>
    <section class="content-panel">
        <div class="page-title-row">
            <h2>Club participation
            </h2><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/reports.php?export=clubs')) ?>">Download CSV</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Club
                        </th>
                        <th>Approved members
                        </th>
                        <th>Pending
                        </th>
                        <th>Sessions
                        </th>
                        <th>Member comparison
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($clubReport as $row): ?>                    <tr>
                        <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['sport']) ?></small>
                        </td>
                        <td><?= $row['approved'] ?>                        </td>
                        <td><?= $row['pending'] ?>                        </td>
                        <td><?= $row['sessions'] ?>                        </td>
                        <td>
<div class="report-bar" role="img" aria-label="<?= $row['approved'] ?> approved members"><span style="width:<?= round($row['approved'] / $maxMembers * 100) ?>%"></span>
                        </div>
                    </td>
                </tr><?php endforeach; ?>            </tbody>
        </table>
    </div>
</section>
<section class="content-panel">
    <div class="page-title-row">
        <h2>Student participation
        </h2><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/reports.php?export=students')) ?>">Download CSV</a>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student
                    </th>
                    <th>Clubs
                    </th>
                    <th>Tournaments
                    </th>
                    <th>Matches
                    </th>
                    <th>Bookings
                    </th>
                    <th>Total
                    </th>
                </tr>
            </thead>
            <tbody><?php foreach ($studentReport as $row): ?>                <tr>
                    <td><strong><?= e($row['full_name']) ?></strong><br><small><?= e($row['student_id']) ?></small>
                    </td>
                    <td><?= $row['clubs'] ?>                    </td>
                    <td><?= $row['tournaments'] ?>                    </td>
                    <td><?= $row['matches'] ?>                    </td>
                    <td><?= $row['bookings'] ?>                    </td>
                    <td><strong><?= $row['clubs'] + $row['tournaments'] + $row['matches'] + $row['bookings'] ?></strong>
                    </td>
                </tr><?php endforeach; ?>            </tbody>
        </table>
    </div>
</section>
<section class="content-panel">
    <h2 class="mb-4">Team performance
    </h2><?php if ($performance): ?>    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Team
                    </th>
                    <th>Played
                    </th>
                    <th>Won
                    </th>
                    <th>Drawn
                    </th>
                    <th>Lost
                    </th>
                    <th>Score for
                    </th>
                    <th>Score against
                    </th>
                </tr>
            </thead>
            <tbody><?php foreach ($performance as $row): ?>                <tr>
                    <td><strong><?= e($row['team']) ?></strong>
                    </td>
                    <td><?= $row['played'] ?>                    </td>
                    <td><?= $row['won'] ?>                    </td>
                    <td><?= $row['drawn'] ?>                    </td>
                    <td><?= $row['lost'] ?>                    </td>
                    <td><?= $row['for'] ?>                    </td>
                    <td><?= $row['against'] ?>                    </td>
                </tr><?php endforeach; ?>            </tbody>
        </table>
    </div><?php else: ?><p>Team performance appears after results are recorded.</p><?php endif; ?></section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
