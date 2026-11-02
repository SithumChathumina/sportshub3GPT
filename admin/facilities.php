<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
$errors = [];
$values = ['id' => '', 'name' => '', 'type' => 'facility', 'location' => '', 'total_units' => '1', 'status' => 'active'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete') {
        run_query('DELETE FROM facilities WHERE id=?', [$id]);
        flash('success', 'Facility or equipment deleted.');
        redirect('admin/facilities.php');
    }
    if ($action === 'save') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $values['id'] = (string) $id;
        if (strlen($values['name']) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        }
        if (!in_array($values['type'], ['facility', 'equipment'], true)) {
            $errors[] = 'Choose a valid type.';
        }
        if ($values['location'] === '') {
            $errors[] = 'Location is required.';
        }
        if ((int) $values['total_units'] < 1 || (int) $values['total_units'] > 500) {
            $errors[] = 'Units must be between 1 and 500.';
        }
        if (!in_array($values['status'], ['active', 'maintenance'], true)) {
            $errors[] = 'Choose a valid status.';
        }
        if (!$errors) {
            $data = [$values['name'], $values['type'], $values['location'], (int) $values['total_units'], $values['status']];
            if ($id) {
                $data[] = $id;
                run_query('UPDATE facilities SET name=?, type=?, location=?, total_units=?, status=? WHERE id=?', $data);
                flash('success', 'Item updated.');
            }
            else {
                run_query('INSERT INTO facilities (name, type, location, total_units, status) VALUES (?, ?, ?, ?, ?)', $data);
                flash('success', 'Item created.');
            }
            redirect('admin/facilities.php');
        }
    }
}
elseif (isset($_GET['edit'])) {
    $facility = fetch_one('SELECT * FROM facilities WHERE id=?', [(int) $_GET['edit']]);
    if ($facility) $values = array_merge($values, $facility);
}
$pageTitle = 'Manage facilities and equipment';
$activePage = 'dashboard';
$adminSection = 'facilities';
$facilities = fetch_all('SELECT * FROM facilities ORDER BY type, name');
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Resource management</span>
    <h1>Facilities and equipment
    </h1><p>Maintain the items students can request and mark unavailable resources.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <div class="page-title-row">
            <h2><?= $values['id'] ? 'Edit item' : 'Add item' ?>            </h2><?php if ($values['id']): ?><a class="link-arrow" href="<?= e(url('admin/facilities.php')) ?>">Cancel editing</a><?php endif; ?>        </div><?php if ($errors): ?>        <div class="alert alert-danger">
            <ul class="mb-0"><?php foreach ($errors as $error): ?>                <li><?= e($error) ?>                </li><?php endforeach; ?>            </ul>
        </div><?php endif; ?>        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($values['id']) ?>">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" value="<?= e($values['name']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="type">Type</label><select class="form-select" id="type" name="type"><option value="facility" <?= $values['type'] === 'facility' ? 'selected' : '' ?>>Facility</option><option value="equipment" <?= $values['type'] === 'equipment' ? 'selected' : '' ?>>Equipment</option></select>
                </div>
                <div class="col-md-4"><label class="form-label" for="location">Location</label><input class="form-control" id="location" name="location" value="<?= e($values['location']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="total_units">Total units</label><input class="form-control" type="number" id="total_units" name="total_units" min="1" max="500" value="<?= e($values['total_units']) ?>" required>
                </div>
                <div class="col-md-4"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Active</option><option value="maintenance" <?= $values['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option></select>
                </div>
                <div class="col-md-4 d-flex align-items-end"><button class="btn btn-sport w-100" type="submit"><?= $values['id'] ? 'Save changes' : 'Add item' ?></button>
                </div>
            </div>
        </form>
    </section>
    <section class="content-panel">
        <h2 class="mb-4">Resource list
        </h2>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name
                        </th>
                        <th>Type
                        </th>
                        <th>Location
                        </th>
                        <th>Units
                        </th>
                        <th>Status
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($facilities as $row): ?>                    <tr>
                        <td><strong><?= e($row['name']) ?></strong>
                        </td>
                        <td><?= e(ucfirst($row['type'])) ?>                        </td>
                        <td><?= e($row['location']) ?>                        </td>
                        <td><?= $row['total_units'] ?>                        </td>
                        <td><?= status_badge($row['status']) ?>                        </td>
                        <td>
                            <div class="table-actions"><a class="btn btn-sm btn-outline-sport" href="<?= e(url('admin/facilities.php?edit=' . $row['id'])) ?>">Edit</a>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $row['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Delete this item and all related bookings?">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div>
    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
