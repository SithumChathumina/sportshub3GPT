<?php
require_once __DIR__ . '/../includes/init.php';
require_student();
$pageTitle = 'My participation history';
$activePage = 'dashboard';
$studentSection = 'history';
$userId = current_user()['id'];
$memberships = fetch_all('SELECT m.*, c.name, c.sport FROM memberships m JOIN clubs c ON c.id = m.club_id WHERE m.user_id = ? ORDER BY m.joined_at DESC', [$userId]);
$tournaments = fetch_all('SELECT tr.*, t.name, t.sport, t.start_date FROM tournament_registrations tr JOIN tournaments t ON t.id = tr.tournament_id WHERE tr.user_id = ? ORDER BY tr.created_at DESC', [$userId]);
$matches = fetch_all('SELECT mr.*, m.home_team, m.away_team, m.sport, m.match_datetime, m.status AS match_status FROM match_registrations mr JOIN matches m ON m.id = mr.match_id WHERE mr.user_id = ? ORDER BY mr.created_at DESC', [$userId]);
$bookings = fetch_all('SELECT b.*, f.name FROM bookings b JOIN facilities f ON f.id = b.facility_id WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 10', [$userId]);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Your sports record</span>
    <h1>Participation history
    </h1><p>A personal record of your club requests, event entries and recent bookings.</p>
</div>
<div class="container dashboard-content">
    <div class="row g-4">
        <div class="col-lg-6">
            <section class="content-panel">
                <h2 class="mb-4">Club memberships
                </h2><?php if ($memberships): ?>                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Club
                                </th>
                                <th>Requested
                                </th>
                                <th>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($memberships as $row): ?>                            <tr>
                                <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['sport']) ?></small>
                                </td>
                                <td><?= format_date($row['joined_at']) ?>                                </td>
                                <td><?= status_badge($row['status']) ?>                                </td>
                            </tr><?php endforeach; ?>                        </tbody>
                    </table>
                </div><?php else: ?><p>No membership requests yet.</p><?php endif; ?>            </section>
        </div>
        <div class="col-lg-6">
            <section class="content-panel">
                <h2 class="mb-4">Tournament entries
                </h2><?php if ($tournaments): ?>                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tournament
                                </th>
                                <th>Team
                                </th>
                                <th>Status
                                </th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($tournaments as $row): ?>                            <tr>
                                <td><strong><?= e($row['name']) ?></strong><br><small><?= format_date($row['start_date']) ?></small>
                                </td>
                                <td><?= e($row['team_name']) ?>                                </td>
                                <td><?= status_badge($row['status']) ?>                                </td>
                            </tr><?php endforeach; ?>                        </tbody>
                    </table>
                </div><?php else: ?><p>No tournament entries yet.</p><?php endif; ?>            </section>
        </div>
    </div>
    <section class="content-panel">
        <h2 class="mb-4">Match entries
        </h2><?php if ($matches): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Match
                        </th>
                        <th>Date
                        </th>
                        <th>Preferred team
                        </th>
                        <th>Registration
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($matches as $row): ?>                    <tr>
                        <td><strong><?= e($row['home_team']) ?> vs <?= e($row['away_team']) ?></strong><br><small><?= e($row['sport']) ?></small>
                        </td>
                        <td><?= format_date($row['match_datetime']) ?>                        </td>
                        <td><?= e($row[$row['preferred_team'] . '_team']) ?>                        </td>
                        <td><?= status_badge($row['status']) ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No match entries yet.</p><?php endif; ?>    </section>
    <section class="content-panel">
        <h2 class="mb-4">Recent booking activity
        </h2><?php if ($bookings): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item
                        </th>
                        <th>Booking date
                        </th>
                        <th>Purpose
                        </th>
                        <th>Status
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($bookings as $row): ?>                    <tr>
                        <td><?= e($row['name']) ?>                        </td>
                        <td><?= format_date($row['booking_date']) ?>                        </td>
                        <td><?= e($row['purpose']) ?>                        </td>
                        <td><?= status_badge($row['status']) ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No booking activity yet.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
