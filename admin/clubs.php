<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$errors = [];
$values = ['id' => '', 'name' => '', 'sport' => '', 'description' => '', 'coach_name' => '', 'meeting_day' => 'Tuesday', 'location' => '', 'max_members' => '30', 'status' => 'active'];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        run_query('DELETE FROM clubs WHERE id = ?', [$id]);
        flash('success', 'Club and its related memberships/sessions were deleted.');
        redirect('admin/clubs.php');
    }
    if ($action === 'save') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $values['id'] = (string) $id;
        if (strlen($values['name']) < 3) {
            $errors[] = 'Club name must be at least 3 characters.';
        }
        if ($values['sport'] === '') {
            $errors[] = 'Sport is required.';
        }
        if (strlen($values['description']) < 10) {
            $errors[] = 'Description must be at least 10 characters.';
        }
        if ($values['coach_name'] === '') {
            $errors[] = 'Coach name is required.';
        }
        if (!in_array($values['meeting_day'], $days, true)) {
            $errors[] = 'Choose a valid meeting day.';
        }
        if ($values['location'] === '') {
            $errors[] = 'Location is required.';
        }
        if ((int) $values['max_members'] < 1 || (int) $values['max_members'] > 500) {
            $errors[] = 'Member limit must be between 1 and 500.';
        }
        if (!in_array($values['status'], ['active', 'inactive'], true)) {
            $errors[] = 'Choose a valid status.';
        }
        if (!$errors) {
            try {
                if ($id) {
                    run_query('UPDATE clubs SET name=?, sport=?, description=?, coach_name=?, meeting_day=?, location=?, max_members=?, status=? WHERE id=?', [$values['name'], $values['sport'], $values['description'], $values['coach_name'], $values['meeting_day'], $values['location'], (int) $values['max_members'], $values['status'], $id]);
                    flash('success', 'Club updated successfully.');
                }
                else {
                    run_query('INSERT INTO clubs (name, sport, description, coach_name, meeting_day, location, max_members, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [$values['name'], $values['sport'], $values['description'], $values['coach_name'], $values['meeting_day'], $values['location'], (int) $values['max_members'], $values['status']]);
                    flash('success', 'New club created successfully.');
                }
                redirect('admin/clubs.php');
            }
            catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $errors[] = 'A club with that name already exists.';
                }
                else throw $exception;
            }
        }
    }
}
elseif (isset($_GET['edit'])) {
    $club = fetch_one('SELECT * FROM clubs WHERE id = ?', [(int) $_GET['edit']]);
    if ($club) $values = array_merge($values, $club);
}
$pageTitle = 'Manage clubs';
$activePage = 'dashboard';
$adminSection = 'clubs';
$clubs = fetch_all("SELECT c.*, (SELECT COUNT(*) FROM memberships m WHERE m.club_id=c.id AND m.status='approved') AS member_count FROM clubs c ORDER BY c.name");
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Club management</span>
    <h1><?= $values['id'] ? 'Edit club' : 'Manage clubs' ?>    </h1><p>Create, update or remove sports clubs and set their membership limits.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2><?= $values['id'] ? 'Edit ' . $values['name'] : 'Add a new club' ?>            </h2><?php if ($values['id']): ?><a class="link-arrow" href="<?= e(url('admin/clubs.php')) ?>">Cancel editing</a><?php endif; ?>        </div><?php if ($errors): ?>        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $error): ?>                <li><?= e($error) ?>                </li><?php endforeach; ?>            </ul>
        </div><?php endif; ?>        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($values['id']) ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label" for="name">Club name</label><input class="form-control" id="name" name="name" value="<?= e($values['name']) ?>" required>
                </div>
                <div class="col-md-6"><label class="form-label" for="sport">Sport</label><input class="form-control" id="sport" name="sport" value="<?= e($values['sport']) ?>" required>
                </div>
                <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" minlength="10" required><?= e($values['description']) ?></textarea>
                </div>
                <div class="col-md-4"><label class="form-label" for="coach_name">Coach</label><input class="form-control" id="coach_name" name="coach_name" value="<?= e($values['coach_name']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="meeting_day">Meeting day</label><select class="form-select" id="meeting_day" name="meeting_day"><?php foreach ($days as $day): ?><option <?= $values['meeting_day'] === $day ? 'selected' : '' ?>><?= $day ?></option><?php endforeach; ?></select>
                </div>
                <div class="col-md-4"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" value="<?= e($values['location']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="max_members">Member limit</label><input class="form-control" type="number" id="max_members" name="max_members" min="1" max="500" value="<?= e($values['max_members']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="inactive" <?= $values['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option></select>
                </div>
                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-sport w-100" type="submit"><?= $values['id'] ? 'Save changes' : 'Create club' ?></button>
                </div>
            </div>
        </form>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">All clubs
        </h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Club
                        </th>
                        <th>Coach
                        </th>
                        <th>Training
                        </th>
                        <th>Members
                        </th>
                        <th>Status
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($clubs as $club): ?>                    <tr>
                        <td><strong><?= e($club['name']) ?></strong><br><small><?= e($club['sport']) ?></small>
                        </td>
                        <td><?= e($club['coach_name']) ?>                        </td>
                        <td><?= e($club['meeting_day']) ?>s<br><small><?= e($club['location']) ?></small>
                        </td>
                        <td><?= $club['member_count'] ?> / <?= $club['max_members'] ?>                        </td>
                        <td><?= status_badge($club['status']) ?>                        </td>
                        <td>
                            <div class="table-actions"><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/clubs.php?edit=' . $club['id'])) ?>">Edit</a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $club['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this club? Related memberships and training sessions will also be deleted.">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
