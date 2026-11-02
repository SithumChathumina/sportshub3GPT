<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$errors = [];
$values = ['id' => '', 'name' => '', 'sport' => '', 'description' => '', 'start_date' => '', 'end_date' => '', 'venue' => '', 'registration_deadline' => '', 'max_teams' => '8', 'status' => 'upcoming'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        run_query('DELETE FROM tournaments WHERE id=?', [$id]);
        flash('success', 'Tournament deleted.');
        redirect('admin/tournaments.php');
    }
    if ($action === 'save') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $values['id'] = (string) $id;
        if (strlen($values['name']) < 3) {
            $errors[] = 'Tournament name must be at least 3 characters.';
        }
        if ($values['sport'] === '') {
            $errors[] = 'Sport is required.';
        }
        if (strlen($values['description']) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        }
        if (!strtotime($values['start_date']) || !strtotime($values['end_date']) || $values['end_date'] < $values['start_date']) {
            $errors[] = 'End date must be on or after the start date.';
        }
        if (!strtotime($values['registration_deadline']) || $values['registration_deadline'] > $values['start_date']) {
            $errors[] = 'Registration deadline must be on or before the start date.';
        }
        if ($values['venue'] === '') {
            $errors[] = 'Venue is required.';
        }
        if ((int) $values['max_teams'] < 2 || (int) $values['max_teams'] > 100) {
            $errors[] = 'Team limit must be between 2 and 100.';
        }
        if (!in_array($values['status'], ['upcoming', 'ongoing', 'completed'], true)) {
            $errors[] = 'Choose a valid status.';
        }
        if (!$errors) {
            $data = [$values['name'], $values['sport'], $values['description'], $values['start_date'], $values['end_date'], $values['venue'], $values['registration_deadline'], (int) $values['max_teams'], $values['status']];
            if ($id) {
                $data[] = $id;
                run_query('UPDATE tournaments SET name=?, sport=?, description=?, start_date=?, end_date=?, venue=?, registration_deadline=?, max_teams=?, status=? WHERE id=?', $data);
                flash('success', 'Tournament updated.');
            }
            else {
                run_query('INSERT INTO tournaments (name, sport, description, start_date, end_date, venue, registration_deadline, max_teams, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $data);
                flash('success', 'Tournament created.');
            }
            redirect('admin/tournaments.php');
        }
    }
}
elseif (isset($_GET['edit'])) {
    $tournament = fetch_one('SELECT * FROM tournaments WHERE id=?', [(int) $_GET['edit']]);
    if ($tournament) $values = array_merge($values, $tournament);
}
$pageTitle = 'Manage tournaments';
$activePage = 'dashboard';
$adminSection = 'tournaments';
$tournaments = fetch_all("SELECT t.*, (SELECT COUNT(DISTINCT team_name) FROM tournament_registrations tr WHERE tr.tournament_id=t.id AND tr.status='registered') AS team_count FROM tournaments t ORDER BY t.start_date DESC");
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Tournament management</span>
    <h1>Tournaments
    </h1><p>Create events, control registration dates and track team entries.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2><?= $values['id'] ? 'Edit tournament' : 'Create tournament' ?>            </h2><?php if ($values['id']): ?><a class="link-arrow" href="<?= e(url('admin/tournaments.php')) ?>">Cancel editing</a><?php endif; ?>        </div><?php if ($errors): ?>        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $error): ?>                <li><?= e($error) ?>                </li><?php endforeach; ?>            </ul>
        </div><?php endif; ?>        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($values['id']) ?>">
            <div class="row g-3">
                <div class="col-md-7"><label class="form-label" for="name">Tournament name</label><input class="form-control" id="name" name="name" value="<?= e($values['name']) ?>" required>
                </div>
                <div class="col-md-5"><label class="form-label" for="sport">Sport</label><input class="form-control" id="sport" name="sport" value="<?= e($values['sport']) ?>" required>
                </div>
                <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" minlength="10" required><?= e($values['description']) ?></textarea>
                </div>
                <div class="col-md-3"><label class="form-label" for="start_date">Start date</label><input class="form-control" type="date" id="start_date" name="start_date" value="<?= e($values['start_date']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="end_date">End date</label><input class="form-control" type="date" id="end_date" name="end_date" value="<?= e($values['end_date']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="registration_deadline">Registration deadline</label><input class="form-control" type="date" id="registration_deadline" name="registration_deadline" value="<?= e($values['registration_deadline']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="venue">Venue</label><input class="form-control" id="venue" name="venue" value="<?= e($values['venue']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="max_teams">Maximum teams</label><input class="form-control" type="number" id="max_teams" name="max_teams" min="2" max="100" value="<?= e($values['max_teams']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><?php foreach (['upcoming', 'ongoing', 'completed'] as $option): ?><option value="<?= $option ?>" <?= $values['status'] === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-sport w-100" type="submit"><?= $values['id'] ? 'Save changes' : 'Create tournament' ?></button>
                </div>
            </div>
        </form>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">Tournament list
        </h2><?php if ($tournaments): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tournament
                        </th>
                        <th>Dates
                        </th>
                        <th>Teams
                        </th>
                        <th>Status
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($tournaments as $row): ?>                    <tr>
                        <td><strong><?= e($row['name']) ?></strong><br><small><?= e($row['sport']) ?> · <?= e($row['venue']) ?></small>
                        </td>
                        <td><?= format_date($row['start_date']) ?> – <?= format_date($row['end_date']) ?><br><small>Register by <?= format_date($row['registration_deadline']) ?></small>
                        </td>
                        <td><?= $row['team_count'] ?> / <?= $row['max_teams'] ?>                        </td>
                        <td><?= status_badge($row['status']) ?>                        </td>
                        <td>
                            <div class="table-actions"><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/tournaments.php?edit=' . $row['id'])) ?>">Edit</a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this tournament and its registrations?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No tournaments yet.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
