<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$pageTitle = 'Admin overview';
$activePage = 'dashboard';
$adminSection = 'dashboard';
$studentCount = (int) fetch_one("SELECT COUNT(*) AS total FROM users WHERE role = 'student'")['total'];
$clubCount = (int) fetch_one('SELECT COUNT(*) AS total FROM clubs')['total'];
$pendingMembers = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE status = 'pending'")['total'];
$pendingBookings = (int) fetch_one("SELECT COUNT(*) AS total FROM bookings WHERE status = 'pending'")['total'];
$upcomingMatches = (int) fetch_one("SELECT COUNT(*) AS total FROM matches WHERE status = 'scheduled' AND match_datetime >= NOW()")['total'];
$recentMemberships = fetch_all("SELECT m.*, u.full_name, u.student_id, c.name AS club_name FROM memberships m JOIN users u ON u.id = m.user_id JOIN clubs c ON c.id = m.club_id ORDER BY m.joined_at DESC LIMIT 6");
$nextMatches = fetch_all("SELECT * FROM matches WHERE status = 'scheduled' AND match_datetime >= NOW() ORDER BY match_datetime LIMIT 5");
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Administrator area</span>
    <h1>SportsHub control room
    </h1><p>Manage clubs, people, fixtures and requests from one dashboard.</p>
</div>
<div class="container dashboard-content">
    <div class="metric-grid">
        <div class="metric-card"><strong><?= $studentCount ?></strong><span>Registered students</span>
        </div>
        <div class="metric-card"><strong><?= $clubCount ?></strong><span>Total sports clubs</span>
        </div>
        <div class="metric-card"><strong><?= $pendingMembers ?></strong><span>Memberships to review</span>
        </div>
        <div class="metric-card"><strong><?= $pendingBookings ?></strong><span>Bookings to review</span>
        </div>
    </div>
    <div class="content-panel">
        <div class="page-title-row">
            <h2>Quick actions
            </h2><span class="required-note"><?= $upcomingMatches ?> upcoming fixtures</span>
        </div>
        <div class="quick-actions"><a class="btn btn-sport" href="<?= e(url('admin/matches.php')) ?>">Schedule a match</a><a class="btn btn-outline-sport" href="<?= e(url('admin/sessions.php')) ?>">Add training</a><a class="btn btn-outline-sport" href="<?= e(url('admin/memberships.php')) ?>">Review memberships</a><a class="btn btn-outline-sport" href="<?= e(url('admin/reports.php')) ?>">View reports</a>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-lg-7">
            <section class="content-panel">
                <div class="page-title-row">
                    <h2>Recent membership requests
                    </h2><a class="link-arrow" href="<?= e(url('admin/memberships.php')) ?>">Review all</a>
                </div><?php if ($recentMemberships): ?>                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student
                                </th>
                                <th>Club
                                </th>
                                <th>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($recentMemberships as $row): ?>                            <tr>
                                <td><strong><?= e($row['full_name']) ?></strong><br><small><?= e($row['student_id']) ?></small>
                                </td>
                                <td><?= e($row['club_name']) ?><br><small><?= format_date($row['joined_at']) ?></small>
                                </td>
                                <td><?= status_badge($row['status']) ?>                                </td>
                            </tr><?php endforeach; ?>                        </tbody>
                    </table>
                </div><?php else: ?><p>No membership requests yet.</p><?php endif; ?>            </section>
        </div>
        <div class="col-lg-5">
            <section class="content-panel">
                <div class="page-title-row">
                    <h2>Next fixtures
                    </h2><a class="link-arrow" href="<?= e(url('admin/matches.php')) ?>">Manage</a>
                </div><?php if ($nextMatches): ?><?php foreach ($nextMatches as $match): ?>                <div class="py-3 border-bottom"><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong><br><small><?= format_date($match['match_datetime']) ?> · <?= format_time($match['match_datetime']) ?><br><?= e($match['venue']) ?></small>
                </div><?php endforeach; ?><?php else: ?><p>No upcoming fixtures.</p><?php endif; ?>            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
