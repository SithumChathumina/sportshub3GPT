<?php
require_once __DIR__ . '/../includes/init.php';
require_student();
$user = current_user();
$pageTitle = 'Student dashboard';
$activePage = 'dashboard';
$studentSection = 'dashboard';
$clubCount = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE user_id = ? AND status = 'approved'", [$user['id']])['total'];
$registrationCount = (int) fetch_one("SELECT (SELECT COUNT(*) FROM tournament_registrations WHERE user_id = ? AND status = 'registered') + (SELECT COUNT(*) FROM match_registrations WHERE user_id = ? AND status = 'registered') AS total", [$user['id'], $user['id']])['total'];
$pendingBookings = (int) fetch_one("SELECT COUNT(*) AS total FROM bookings WHERE user_id = ? AND status = 'pending'", [$user['id']])['total'];
$pendingMemberships = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE user_id = ? AND status = 'pending'", [$user['id']])['total'];
$myClubs = fetch_all("SELECT c.name, c.sport, c.location, m.status FROM memberships m JOIN clubs c ON c.id = m.club_id WHERE m.user_id = ? ORDER BY m.joined_at DESC", [$user['id']]);
$upcoming = fetch_all("SELECT s.*, c.name AS club_name FROM training_sessions s JOIN clubs c ON c.id = s.club_id JOIN memberships m ON m.club_id = c.id WHERE m.user_id = ? AND m.status = 'approved' AND s.session_date >= CURDATE() ORDER BY s.session_date, s.start_time LIMIT 4", [$user['id']]);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Student area</span>
    <h1>Hi, <?= e(explode(' ', $user['full_name'])[0]) ?>.
    </h1><p>Here is what is happening across your NSBM sports activities.</p>
</div>
<div class="container dashboard-content">
    <div class="metric-grid">
        <div class="metric-card"><strong><?= $clubCount ?></strong><span>Approved club memberships</span>
        </div>
        <div class="metric-card"><strong><?= $registrationCount ?></strong><span>Active event registrations</span>
        </div>
        <div class="metric-card"><strong><?= $pendingBookings ?></strong><span>Pending booking requests</span>
        </div>
        <div class="metric-card"><strong><?= $pendingMemberships ?></strong><span>Memberships awaiting approval</span>
        </div>
    </div>
    <div class="content-panel">
        <div class="page-title-row">
            <h2>Quick actions
            </h2>
        </div>
        <div class="quick-actions"><a class="btn btn-sport" href="<?= e(url('clubs.php')) ?>">Find a club</a><a class="btn btn-outline-sport" href="<?= e(url('student/registrations.php')) ?>">Enter an event</a><a class="btn btn-outline-sport" href="<?= e(url('student/bookings.php')) ?>">Book a facility</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <section class="content-panel">
                <div class="page-title-row">
                    <h2>Next training sessions
                    </h2><a class="link-arrow" href="<?= e(url('student/calendar.php')) ?>">Full schedule</a>
                </div><?php if ($upcoming): ?>                <ul class="schedule-list"><?php foreach ($upcoming as $session): ?>                    <li class="schedule-item">
                        <div class="date-block"><strong><?= format_date($session['session_date'], 'd') ?></strong><span><?= format_date($session['session_date'], 'M') ?></span>
                        </div>
                        <div>
                            <h3><?= e($session['title']) ?>                            </h3><p><?= e($session['club_name']) ?> · <?= format_time($session['start_time']) ?> · <?= e($session['venue']) ?></p>
                        </div>
                    </li><?php endforeach; ?>                </ul><?php else: ?><p class="mb-0">No sessions yet. Join an approved club to see its training schedule.</p><?php endif; ?>            </section>
        </div>
        <div class="col-lg-5">
            <section class="content-panel">
                <h2 class="mb-4">My clubs
                </h2><?php if ($myClubs): ?>                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Club
                                </th>
                                <th>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($myClubs as $club): ?>                            <tr>
                                <td><strong><?= e($club['name']) ?></strong><br><small><?= e($club['location']) ?></small>
                                </td>
                                <td><?= status_badge($club['status']) ?>                                </td>
                            </tr><?php endforeach; ?>                        </tbody>
                    </table>
                </div><?php else: ?><p>You have not requested a club membership yet.</p><a class="link-arrow" href="<?= e(url('clubs.php')) ?>">Browse clubs</a><?php endif; ?>            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
