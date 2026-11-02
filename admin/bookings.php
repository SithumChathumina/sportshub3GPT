<?php
require_once __DIR__ . '/../includes/init.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $note = trim($_POST['admin_note'] ?? '');
    $booking = fetch_one('SELECT b.*, f.total_units FROM bookings b JOIN facilities f ON f.id=b.facility_id WHERE b.id=?', [$id]);
    if (!$booking || !in_array($status, ['approved', 'rejected'], true)) {
        flash('danger', 'Invalid booking update.');
    }
    elseif (strlen($note) > 255) {
        flash('danger', 'Admin note cannot exceed 255 characters.');
    }
    elseif ($status === 'approved') {
        $conflict = fetch_one("SELECT COALESCE(SUM(quantity),0) AS booked FROM bookings WHERE facility_id=? AND booking_date=? AND status='approved' AND id<>? AND start_time<? AND end_time>?", [$booking['facility_id'], $booking['booking_date'], $id, $booking['end_time'], $booking['start_time']]);
        if ((int) $conflict['booked'] + (int) $booking['quantity'] > (int) $booking['total_units']) flash('warning', 'This booking conflicts with an approved booking at the same time.');
        else {
            run_query("UPDATE bookings SET status='approved', admin_note=? WHERE id=?", [$note ?: null, $id]);
            flash('success', 'Booking approved.');
        }
    }
    else {
        run_query("UPDATE bookings SET status='rejected', admin_note=? WHERE id=?", [$note ?: null, $id]);
        flash('success', 'Booking rejected.');
    }
    redirect('admin/bookings.php');
}
$pageTitle = 'Manage booking requests';
$activePage = 'dashboard';
$adminSection = 'bookings';
$status = $_GET['status'] ?? '';
$allowed = ['pending', 'approved', 'rejected', 'cancelled'];
$params = [];
$where = '';
if (in_array($status, $allowed, true)) {
    $where = 'WHERE b.status=?';
    $params[] = $status;
}
$bookings = fetch_all("SELECT b.*, u.full_name, u.student_id, f.name, f.type, f.location FROM bookings b JOIN users u ON u.id=b.user_id JOIN facilities f ON f.id=b.facility_id $where ORDER BY FIELD(b.status,'pending','approved','rejected','cancelled'), b.booking_date DESC", $params);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Booking management</span>
    <h1>Facility requests
    </h1><p>Review requests, prevent double bookings and leave a note for the student.</p>
</div>
<div class="container dashboard-content">
    <section class="content-panel">
        <form class="filter-bar" method="get">
            <div><label class="visually-hidden" for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All booking statuses</option><?php foreach ($allowed as $option): ?><option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= ucfirst($option) ?></option><?php endforeach; ?></select>
            </div>
            <div>
            </div><button class="btn btn-sport" type="submit">Filter bookings</button>
        </form><?php if ($bookings): ?>        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student
                        </th>
                        <th>Resource
                        </th>
                        <th>Date and time
                        </th>
                        <th>Purpose
                        </th>
                        <th>Status
                        </th>
                        <th>Review
                        </th>
                    </tr>
                </thead>
                <tbody><?php foreach ($bookings as $row): ?>                    <tr>
                        <td><strong><?= e($row['full_name']) ?></strong><br><small><?= e($row['student_id']) ?></small>
                        </td>
                        <td><?= e($row['name']) ?><br><small><?= e($row['type']) ?> · Qty <?= $row['quantity'] ?></small>
                        </td>
                        <td><?= format_date($row['booking_date']) ?><br><small><?= format_time($row['start_time']) ?>–<?= format_time($row['end_time']) ?></small>
                        </td>
                        <td><?= e($row['purpose']) ?>                        </td>
                        <td><?= status_badge($row['status']) ?><?php if ($row['admin_note']): ?><br><small><?= e($row['admin_note']) ?></small><?php endif; ?>                        </td>
                        <td><?php if ($row['status'] !== 'cancelled'): ?>                            <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $row['id'] ?>"><label class="visually-hidden" for="note-<?= $row['id'] ?>">Admin note</label><input class="form-control form-control-sm mb-2" id="note-<?= $row['id'] ?>" name="admin_note" maxlength="255" value="<?= e($row['admin_note']) ?>" placeholder="Optional note">
                                <div class="table-actions"><button class="btn btn-sm btn-sport" type="submit" name="status" value="approved">Approve</button><button class="btn btn-sm btn-outline-danger" type="submit" name="status" value="rejected">Reject</button>
                                </div>
                            </form><?php else: ?>—<?php endif; ?>                        </td>
                    </tr><?php endforeach; ?>                </tbody>
            </table>
        </div><?php else: ?><p>No booking requests match this filter.</p><?php endif; ?>    </section>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
