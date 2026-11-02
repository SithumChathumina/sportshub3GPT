<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$errors = [];
$values = ['id' => '', 'club_id' => '', 'title' => '', 'session_date' => '', 'start_time' => '15:00', 'end_time' => '17:00', 'venue' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        run_query('DELETE FROM training_sessions WHERE id=?', [$id]);
        flash('success', 'Training session deleted.');
        redirect('admin/sessions.php');
    }
    if ($action === 'save') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $values['id'] = (string) $id;
        if (!fetch_one('SELECT id FROM clubs WHERE id=?', [(int) $values['club_id']])) {
            $errors[] = 'Choose a valid club.';
        }
        if (strlen($values['title']) < 3) {
            $errors[] = 'Session title must be at least 3 characters.';
        }
        if (!strtotime($values['session_date'])) {
            $errors[] = 'Choose a valid session date.';
        }
        if (!$values['start_time'] || !$values['end_time'] || strtotime($values['end_time']) <= strtotime($values['start_time'])) {
            $errors[] = 'End time must be after the start time.';
        }
        if ($values['venue'] === '') {
            $errors[] = 'Venue is required.';
        }
        if (!$errors) {
            if ($id) {
                run_query('UPDATE training_sessions SET club_id=?, title=?, session_date=?, start_time=?, end_time=?, venue=? WHERE id=?', [(int) $values['club_id'], $values['title'], $values['session_date'], $values['start_time'], $values['end_time'], $values['venue'], $id]);
                flash('success', 'Training session updated.');
            }
            else {
                run_query('INSERT INTO training_sessions (club_id, title, session_date, start_time, end_time, venue) VALUES (?, ?, ?, ?, ?, ?)', [(int) $values['club_id'], $values['title'], $values['session_date'], $values['start_time'], $values['end_time'], $values['venue']]);
                flash('success', 'Training session scheduled.');
            }
            redirect('admin/sessions.php');
        }
    }
}
elseif (isset($_GET['edit'])) {
    $session = fetch_one('SELECT * FROM training_sessions WHERE id=?', [(int) $_GET['edit']]);
    if ($session) $values = array_merge($values, $session);
}
$pageTitle = 'Manage training';
$activePage = 'dashboard';
$adminSection = 'sessions';
$clubs = fetch_all("SELECT id, name FROM clubs WHERE status='active' ORDER BY name");
$sessions = fetch_all('SELECT s.*, c.name AS club_name FROM training_sessions s JOIN clubs c ON c.id=s.club_id ORDER BY s.session_date DESC, s.start_time DESC');
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Training management</span>
    <h1>Training sessions
    </h1><p>Schedule weekly practice and update students automatically through their calendars.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2><?= $values['id'] ? 'Edit session' : 'Schedule a session' ?>            </h2><?php if ($values['id']): ?><a class="link-arrow" href="<?= e(url('admin/sessions.php')) ?>">Cancel editing</a><?php endif; ?>        </div><?php if ($errors): ?>        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $error): ?>                <li><?= e($error) ?>                </li><?php endforeach; ?>            </ul>
        </div><?php endif; ?>        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($values['id']) ?>">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="club_id">Club</label><select class="form-select" id="club_id" name="club_id" required><option value="">Choose club</option><?php foreach ($clubs as $club): ?><option value="<?= $club['id'] ?>" <?= $values['club_id'] == $club['id'] ? 'selected' : '' ?>><?= e($club['name']) ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-8"><label class="form-label" for="title">Session title</label><input class="form-control" id="title" name="title" value="<?= e($values['title']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="session_date">Date</label><input class="form-control" type="date" id="session_date" name="session_date" value="<?= e($values['session_date']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="start_time">Starts</label><input class="form-control" type="time" id="start_time" name="start_time" value="<?= e($values['start_time']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="end_time">Ends</label><input class="form-control" type="time" id="end_time" name="end_time" value="<?= e($values['end_time']) ?>" required>
                </div>
                <div class="col-md-3"><label class="form-label" for="venue">Venue</label><input class="form-control" id="venue" name="venue" value="<?= e($values['venue']) ?>" required>
                </div>
                <div class="col-12"><button class="btn btn-sport" type="submit"><?= $values['id'] ? 'Save changes' : 'Schedule training' ?></button>
                </div>
            </div>
        </form>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">Session list
        </h2><?php if ($sessions): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date
                        </th>
                        <th>Session
                        </th>
                        <th>Club
                        </th>
                        <th>Time
                        </th>
                        <th>Venue
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($sessions as $session): ?>                    <tr>
                        <td><?= format_date($session['session_date']) ?>                        </td>
                        <td><strong><?= e($session['title']) ?></strong>
                        </td>
                        <td><?= e($session['club_name']) ?>                        </td>
                        <td><?= format_time($session['start_time']) ?>–<?= format_time($session['end_time']) ?>                        </td>
                        <td><?= e($session['venue']) ?>                        </td>
                        <td>
                            <div class="table-actions"><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/sessions.php?edit=' . $session['id'])) ?>">Edit</a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $session['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this training session?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No training sessions have been scheduled.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
