<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $membership = fetch_one('SELECT m.*, c.max_members FROM memberships m JOIN clubs c ON c.id=m.club_id WHERE m.id = ?', [$id]);
    if (!$membership || !in_array($status, ['approved', 'rejected'], true)) {
        flash('danger', 'Invalid membership update.');
    }
    elseif ($status === 'approved') {
        $count = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE club_id=? AND status='approved' AND id<>?", [$membership['club_id'], $id])['total'];
        if ($count >= (int) $membership['max_members']) flash('warning', 'This club has reached its member limit.');
        else {
            run_query("UPDATE memberships SET status='approved', reviewed_at=NOW() WHERE id=?", [$id]);
            flash('success', 'Membership approved.');
        }
    }
    else {
        run_query("UPDATE memberships SET status='rejected', reviewed_at=NOW() WHERE id=?", [$id]);
        flash('success', 'Membership rejected.');
    }
    redirect('admin/memberships.php');
}
$pageTitle = 'Membership approvals';
$activePage = 'dashboard';
$adminSection = 'memberships';
$status = $_GET['status'] ?? '';
$allowed = ['pending', 'approved', 'rejected'];
$params = [];
$where = '';
if (in_array($status, $allowed, true)) {
    $where = 'WHERE m.status = ?';
    $params[] = $status;
}
$memberships = fetch_all("SELECT m.*, u.full_name, u.student_id, u.email, c.name AS club_name, c.sport FROM memberships m JOIN users u ON u.id=m.user_id JOIN clubs c ON c.id=m.club_id $where ORDER BY FIELD(m.status, 'pending', 'approved', 'rejected'), m.joined_at DESC", $params);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Membership management</span>
    <h1>Club requests
    </h1><p>Approve or reject student requests to join sports clubs.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <form class="filter-bar" method="get">
            <div><label class="visually-hidden" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All membership statuses</option><?php foreach ($allowed as $option): ?><option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach; ?></select>
            </div>
            <div>
            </div><button class="btn btn-sport" type="submit">Filter requests</button>
        </form><?php if ($memberships): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student
                        </th>
                        <th>Club
                        </th>
                        <th>Requested
                        </th>
                        <th>Status
                        </th>
                        <th>Actions
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($memberships as $row): ?>                    <tr>
                        <td><strong><?= e($row['full_name']) ?></strong><br><small><?= e($row['student_id']) ?> · <?= e($row['email']) ?></small>
                        </td>
                        <td><?= e($row['club_name']) ?><br><small><?= e($row['sport']) ?></small>
                        </td>
                        <td><?= format_date($row['joined_at']) ?>                        </td>
                        <td><?= status_badge($row['status']) ?>                        </td>
                        <td>
                            <div class="table-actions"><?php if ($row['status'] !== 'approved'): ?>                                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-sport" type="submit">Approve</button>
                                </form><?php endif; ?><?php if ($row['status'] !== 'rejected'): ?>                                <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><input type="hidden" name="status" value="rejected"><button class="btn btn-sm btn-outline-danger" type="submit">Reject</button>
                                </form><?php endif; ?>                            </div>
                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No requests match this filter.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
