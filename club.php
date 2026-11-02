<?php
require_once __DIR__ . '/includes/init.php';
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (int) ($_POST['club_id'] ?? 0);
$club = fetch_one('SELECT * FROM clubs WHERE id = ?', [$id]);
if (!$club) {
    http_response_code(404);
    $pageTitle = 'Club not found';
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-content"><div class="container"><div class="empty-state"><h1>Club not found</h1><p>The club may have been removed.</p><a class="btn btn-sport" href="' . e(url('clubs.php')) . '">Back to clubs</a></div></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'join') {
    require_student();
    verify_csrf();
    if ($club['status'] !== 'active') {
        flash('warning', 'This club is not accepting new members.');
    }
    else {
        $memberCount = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE club_id = ? AND status = 'approved'", [$id])['total'];
        $existing = fetch_one('SELECT * FROM memberships WHERE user_id = ? AND club_id = ?', [current_user()['id'], $id]);
        if ($existing && in_array($existing['status'], ['pending', 'approved'])) {
            flash('info', 'Your membership is already ' . $existing['status'] . '.');
        }
        elseif ($memberCount >= (int) $club['max_members']) {
            flash('warning', 'This club has reached its member limit.');
        }
        elseif ($existing) {
            run_query("UPDATE memberships SET status = 'pending', joined_at = NOW(), reviewed_at = NULL WHERE id = ?", [$existing['id']]);
            flash('success', 'Your membership request has been sent again.');
        }
        else {
            run_query('INSERT INTO memberships (user_id, club_id) VALUES (?, ?)', [current_user()['id'], $id]);
            flash('success', 'Your membership request has been sent for approval.');
        }
    }
    redirect('club.php?id=' . $id);
}
$pageTitle = $club['name'];
$activePage = 'clubs';
$membership = is_logged_in() && !is_admin() ? fetch_one('SELECT * FROM memberships WHERE user_id = ? AND club_id = ?', [current_user()['id'], $id]) : false;
$memberCount = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE club_id = ? AND status = 'approved'", [$id])['total'];
$sessions = fetch_all('SELECT * FROM training_sessions WHERE club_id = ? AND session_date >= CURDATE() ORDER BY session_date, start_time LIMIT 5', [$id]);
$matches = fetch_all("SELECT * FROM matches WHERE sport = ? AND status = 'scheduled' AND match_datetime >= NOW() ORDER BY match_datetime LIMIT 4", [$club['sport']]);
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container"><span class="hero-kicker"><?= e($club['sport']) ?> club</span>
        <h1><?= e($club['name']) ?>        </h1><p><?= e($club['description']) ?></p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="content-panel">
                    <div class="page-title-row">
                        <h2>Upcoming training
                        </h2><a href="<?= e(url('student/calendar.php')) ?>" class="link-arrow">My schedule</a>
                    </div>
                <?php if ($sessions): ?>                    <ul class="schedule-list"><?php foreach ($sessions as $session): ?>                        <li class="schedule-item">
                            <div class="date-block"><strong><?= format_date($session['session_date'], 'd') ?></strong><span><?= format_date($session['session_date'], 'M') ?></span>
                            </div>
                            <div>
                                <h3><?= e($session['title']) ?>                                </h3><p><?= format_time($session['start_time']) ?>–<?= format_time($session['end_time']) ?> · <?= e($session['venue']) ?></p>
                            </div><span><?= status_badge('scheduled') ?></span>
                        </li><?php endforeach; ?>                    </ul><?php else: ?><p class="mb-0">No upcoming sessions have been posted yet.</p><?php endif; ?>
                </div>
                <div class="content-panel">
                    <h2 class="mb-4">Related fixtures
                    </h2>
                <?php if ($matches): ?>                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Match
                                    </th>
                                    <th>Date
                                    </th>
                                    <th>Venue
                                    </th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($matches as $match): ?>                                <tr>
                                    <td><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?>                                    </td>
                                    <td><?= format_date($match['match_datetime']) ?>, <?= format_time($match['match_datetime']) ?>                                    </td>
                                    <td><?= e($match['venue']) ?>                                    </td>
                                </tr><?php endforeach; ?>                            </tbody>
                        </table>
                    </div><?php else: ?><p class="mb-0">No related fixtures are scheduled.</p><?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
            <aside class="content-panel">
                <h2>Club details
                </h2>
                <dl class="row mt-4"><dt class="col-5">Coach</dt><dd class="col-7"><?= e($club['coach_name']) ?></dd><dt class="col-5">Training</dt><dd class="col-7"><?= e($club['meeting_day']) ?>s</dd><dt class="col-5">Location</dt><dd class="col-7"><?= e($club['location']) ?></dd><dt class="col-5">Members</dt><dd class="col-7"><?= $memberCount ?> / <?= $club['max_members'] ?></dd></dl>
                <?php if ($membership): ?>                <div class="mb-3">Your request: <?= status_badge($membership['status']) ?>                </div><?php endif; ?>
                <?php if (!is_logged_in()): ?><a class="btn btn-sport w-100" href="<?= e(url('login.php')) ?>">Log in to join</a><?php elseif (is_admin()): ?><a class="btn btn-outline-sport w-100" href="<?= e(url('admin/clubs.php')) ?>">Manage in admin</a><?php elseif (!$membership || $membership['status'] === 'rejected'): ?>                <form method="post"><?= csrf_field() ?><input type="hidden" name="club_id" value="<?= $club['id'] ?>"><input type="hidden" name="action" value="join"><button class="btn btn-sport w-100" type="submit"><?= $membership ? 'Request again' : 'Request to join' ?></button>
                </form><?php endif; ?>
            </aside>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
