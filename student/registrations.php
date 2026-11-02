<?php
require_once __DIR__ . '/../includes/init.php';
require_student();
$userId = current_user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['register_tournament', 'withdraw_tournament'])) {
        $tournamentId = (int) ($_POST['tournament_id'] ?? 0);
        $tournament = fetch_one('SELECT * FROM tournaments WHERE id = ?', [$tournamentId]);
        if (!$tournament) {
            flash('danger', 'Tournament not found.');
        }
        elseif ($action === 'withdraw_tournament') {
            run_query("UPDATE tournament_registrations SET status = 'withdrawn' WHERE tournament_id = ? AND user_id = ?", [$tournamentId, $userId]);
            flash('success', 'Your tournament registration has been withdrawn.');
        }
        elseif ($tournament['status'] !== 'upcoming' || $tournament['registration_deadline'] < date('Y-m-d')) {
            flash('warning', 'Registration for this tournament is closed.');
        }
        else {
            $teamName = trim($_POST['team_name'] ?? '');
            if (strlen($teamName) < 2 || strlen($teamName) > 100) {
                flash('danger', 'Enter a team name between 2 and 100 characters.');
                redirect('student/registrations.php');
            }
            $existing = fetch_one('SELECT * FROM tournament_registrations WHERE tournament_id = ? AND user_id = ?', [$tournamentId, $userId]);
            $teamCount = (int) fetch_one("SELECT COUNT(DISTINCT team_name) AS total FROM tournament_registrations WHERE tournament_id = ? AND status = 'registered'", [$tournamentId])['total'];
            if ($existing && $existing['status'] === 'registered') {
                flash('info', 'You are already registered for this tournament.');
            }
            elseif ($teamCount >= (int) $tournament['max_teams']) {
                flash('warning', 'This tournament has reached its team limit.');
            }
            elseif ($existing) {
                run_query("UPDATE tournament_registrations SET team_name = ?, status = 'registered', created_at = NOW() WHERE id = ?", [$teamName, $existing['id']]);
                flash('success', 'Your tournament registration is active again.');
            }
            else {
                run_query('INSERT INTO tournament_registrations (tournament_id, user_id, team_name) VALUES (?, ?, ?)', [$tournamentId, $userId, $teamName]);
                flash('success', 'You have registered for ' . $tournament['name'] . '.');
            }
        }
    }
    elseif (in_array($action, ['register_match', 'withdraw_match'])) {
        $matchId = (int) ($_POST['match_id'] ?? 0);
        $match = fetch_one('SELECT * FROM matches WHERE id = ?', [$matchId]);
        if (!$match) {
            flash('danger', 'Match not found.');
        }
        elseif ($action === 'withdraw_match') {
            run_query("UPDATE match_registrations SET status = 'withdrawn' WHERE match_id = ? AND user_id = ?", [$matchId, $userId]);
            flash('success', 'Your match registration has been withdrawn.');
        }
        elseif ($match['status'] !== 'scheduled' || strtotime($match['match_datetime']) < time()) {
            flash('warning', 'Registration for this match is closed.');
        }
        else {
            $team = $_POST['preferred_team'] ?? '';
            if (!in_array($team, ['home', 'away'], true)) {
                flash('danger', 'Choose a preferred team.');
                redirect('student/registrations.php');
            }
            $existing = fetch_one('SELECT * FROM match_registrations WHERE match_id = ? AND user_id = ?', [$matchId, $userId]);
            if ($existing && $existing['status'] === 'registered') {
                flash('info', 'You are already registered for this match.');
            }
            elseif ($existing) {
                run_query("UPDATE match_registrations SET preferred_team = ?, status = 'registered', created_at = NOW() WHERE id = ?", [$team, $existing['id']]);
                flash('success', 'Your match registration is active again.');
            }
            else {
                run_query('INSERT INTO match_registrations (match_id, user_id, preferred_team) VALUES (?, ?, ?)', [$matchId, $userId, $team]);
                flash('success', 'You have registered for the match.');
            }
        }
    }
    redirect('student/registrations.php');
}
$pageTitle = 'Event registrations';
$activePage = 'dashboard';
$studentSection = 'registrations';
$tournaments = fetch_all("SELECT t.*, tr.status AS registration_status, tr.team_name FROM tournaments t LEFT JOIN tournament_registrations tr ON tr.tournament_id = t.id AND tr.user_id = ? WHERE t.end_date >= CURDATE() ORDER BY t.start_date", [$userId]);
$matches = fetch_all("SELECT m.*, mr.status AS registration_status, mr.preferred_team FROM matches m LEFT JOIN match_registrations mr ON mr.match_id = m.id AND mr.user_id = ? WHERE m.status = 'scheduled' AND m.match_datetime >= NOW() ORDER BY m.match_datetime", [$userId]);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Get in the game</span>
    <h1>Event registrations
    </h1><p>Enter an upcoming tournament or put your name forward for a match.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2>Tournaments
            </h2><span class="required-note">Register before the listed deadline</span>
        </div>
        <?php if ($tournaments): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tournament
                        </th>
                        <th>Dates
                        </th>
                        <th>Registration
                        </th>
                        <th>Your entry
                        </th>
                        <th>Action
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($tournaments as $tournament): ?>                    <tr>
                        <td><strong><?= e($tournament['name']) ?></strong><br><small><?= e($tournament['sport']) ?> · <?= e($tournament['venue']) ?></small>
                        </td>
                        <td><?= format_date($tournament['start_date']) ?><br><small>to <?= format_date($tournament['end_date']) ?></small>
                        </td>
                        <td>Deadline: <?= format_date($tournament['registration_deadline']) ?><br><?= status_badge($tournament['status']) ?>                        </td>
                        <td><?php if ($tournament['registration_status']): ?><?= status_badge($tournament['registration_status']) ?><br><small><?= e($tournament['team_name']) ?></small><?php else: ?><span class="text-muted">Not entered</span><?php endif; ?>                        </td>
                        <td>
            <?php if ($tournament['registration_status'] === 'registered'): ?>                            <form method="post"><?= csrf_field() ?><input type="hidden" name="tournament_id" value="<?= $tournament['id'] ?>"><input type="hidden" name="action" value="withdraw_tournament"><button class="btn btn-sm btn-outline-danger" type="submit">Withdraw</button>
                            </form>
            <?php elseif ($tournament['status'] === 'upcoming' && $tournament['registration_deadline'] >= date('Y-m-d')): ?>                            <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="tournament_id" value="<?= $tournament['id'] ?>"><input type="hidden" name="action" value="register_tournament"><label class="visually-hidden" for="team-<?= $tournament['id'] ?>">Team name</label><input class="form-control form-control-sm mb-2" id="team-<?= $tournament['id'] ?>" name="team_name" placeholder="Team name" minlength="2" maxlength="100" required><button class="btn btn-sm btn-sport" type="submit">Register</button>
                            </form>
            <?php else: ?><span class="text-muted">Closed</span><?php endif; ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No tournaments are currently open.</p><?php endif; ?>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">Match registration
        </h2>
        <?php if ($matches): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fixture
                        </th>
                        <th>Date
                        </th>
                        <th>Your entry
                        </th>
                        <th>Action
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($matches as $match): ?>                    <tr>
                        <td><strong><?= e($match['home_team']) ?> vs <?= e($match['away_team']) ?></strong><br><small><?= e($match['sport']) ?> · <?= e($match['venue']) ?></small>
                        </td>
                        <td><?= format_date($match['match_datetime']) ?><br><small><?= format_time($match['match_datetime']) ?></small>
                        </td>
                        <td><?php if ($match['registration_status']): ?><?= status_badge($match['registration_status']) ?><?php if ($match['registration_status'] === 'registered'): ?><br><small><?= e($match[$match['preferred_team'] . '_team']) ?></small><?php endif; ?><?php else: ?><span class="text-muted">Not entered</span><?php endif; ?>                        </td>
                        <td>
            <?php if ($match['registration_status'] === 'registered'): ?>                            <form method="post"><?= csrf_field() ?><input type="hidden" name="match_id" value="<?= $match['id'] ?>"><input type="hidden" name="action" value="withdraw_match"><button class="btn btn-sm btn-outline-danger" type="submit">Withdraw</button>
                            </form>
            <?php else: ?>                            <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="match_id" value="<?= $match['id'] ?>"><input type="hidden" name="action" value="register_match"><label class="visually-hidden" for="preferred-<?= $match['id'] ?>">Preferred team</label><select class="form-select form-select-sm mb-2" id="preferred-<?= $match['id'] ?>" name="preferred_team" required><option value="">Choose team</option><option value="home"><?= e($match['home_team']) ?></option><option value="away"><?= e($match['away_team']) ?></option></select><button class="btn btn-sm btn-sport" type="submit">Register</button>
                            </form><?php endif; ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No matches are open for registration.</p><?php endif; ?>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
