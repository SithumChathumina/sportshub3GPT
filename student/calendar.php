<?php
require_once __DIR__ . '/../includes/init.php';
require_student();
$pageTitle = 'My training schedule';
$activePage = 'dashboard';
$studentSection = 'calendar';
$userId = current_user()['id'];
$sessions = fetch_all("SELECT s.*, c.name AS club_name, c.sport FROM training_sessions s JOIN clubs c ON c.id = s.club_id JOIN memberships m ON m.club_id = c.id WHERE m.user_id = ? AND m.status = 'approved' AND s.session_date >= CURDATE() ORDER BY s.session_date, s.start_time", [$userId]);
$matches = fetch_all("SELECT m.*, mr.preferred_team FROM match_registrations mr JOIN matches m ON m.id = mr.match_id WHERE mr.user_id = ? AND mr.status = 'registered' AND m.status = 'scheduled' AND m.match_datetime >= NOW() ORDER BY m.match_datetime", [$userId]);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Plan your week</span>
    <h1>My schedule
    </h1><p>Training from your approved clubs and matches you have registered for.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2>Training sessions
            </h2><a class="link-arrow" href="<?= e(url('clubs.php')) ?>">Join another club</a>
        </div><?php if ($sessions): ?>        <ul class="schedule-list"><?php foreach ($sessions as $session): ?>            <li class="schedule-item">
                <div class="date-block"><strong><?= format_date($session['session_date'], 'd') ?></strong><span><?= format_date($session['session_date'], 'M Y') ?></span>
                </div>
                <div>
                    <h3><?= e($session['title']) ?>                    </h3><p><?= e($session['club_name']) ?> · <?= e($session['sport']) ?></p><p><?= format_time($session['start_time']) ?>–<?= format_time($session['end_time']) ?> · <?= e($session['venue']) ?></p>
                </div><span><?= status_badge('scheduled') ?></span>
            </li><?php endforeach; ?>        </ul><?php else: ?>        <div class="empty-state">
            <h3>No training scheduled
            </h3><p>Sessions appear here after your club membership is approved.</p>
        </div><?php endif; ?>    </section>
    <section class="content-panel">
        <h2 class="mb-4">My match fixtures
        </h2><?php if ($matches): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date
                        </th>
                        <th>Match
                        </th>
                        <th>Preferred team
                        </th>
                        <th>Venue
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($matches as $match): ?>                    <tr>
                        <td><?= format_date($match['match_datetime']) ?><br><small><?= format_time($match['match_datetime']) ?></small>
                        </td>
                        <td><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong><br><small><?= e($match['sport']) ?></small>
                        </td>
                        <td><?= e($match[$match['preferred_team'] . '_team']) ?>                        </td>
                        <td><?= e($match['venue']) ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p class="mb-0">You have not registered for an upcoming match.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
