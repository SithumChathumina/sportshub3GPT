<?php
require_once __DIR__ . '/../includes/init.php';
require_student();
$userId = current_user()['id'];
$errors = [];
$values = ['facility_id' => '', 'booking_date' => '', 'start_time' => '', 'end_time' => '', 'quantity' => '1', 'purpose' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'cancel') {
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $statement = run_query("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status IN ('pending', 'approved')", [$bookingId, $userId]);
        flash($statement->rowCount() ? 'success' : 'warning', $statement->rowCount() ? 'Booking request cancelled.' : 'This booking could not be cancelled.');
        redirect('student/bookings.php');
    }
    if ($action === 'create') {
        foreach (array_keys($values) as $key) $values[$key] = trim($_POST[$key] ?? '');
        $facility = fetch_one('SELECT * FROM facilities WHERE id = ?', [(int) $values['facility_id']]);
        $quantity = (int) $values['quantity'];
        if (!$facility || $facility['status'] !== 'active') {
            $errors[] = 'Choose an available facility or equipment item.';
        }
        if (!$values['booking_date'] || $values['booking_date'] < date('Y-m-d')) {
            $errors[] = 'Choose today or a future booking date.';
        }
        if (!$values['start_time'] || !$values['end_time'] || strtotime($values['end_time']) <= strtotime($values['start_time'])) {
            $errors[] = 'End time must be after the start time.';
        }
        if ($quantity < 1 || ($facility && $quantity > (int) $facility['total_units'])) {
            $errors[] = 'Quantity must be between 1 and the available units.';
        }
        if (strlen($values['purpose']) < 3 || strlen($values['purpose']) > 255) {
            $errors[] = 'Describe the booking purpose in 3–255 characters.';
        }
        if (!$errors) {
            $conflict = fetch_one("SELECT COALESCE(SUM(quantity), 0) AS booked FROM bookings WHERE facility_id = ? AND booking_date = ? AND status IN ('pending', 'approved') AND start_time < ? AND end_time > ?", [(int) $values['facility_id'], $values['booking_date'], $values['end_time'], $values['start_time']]);
            if ((int) $conflict['booked'] + $quantity > (int) $facility['total_units']) {
                $errors[] = 'The requested time overlaps another booking. Try a different time or quantity.';
            }
        }
        if (!$errors) {
            run_query('INSERT INTO bookings (user_id, facility_id, booking_date, start_time, end_time, quantity, purpose) VALUES (?, ?, ?, ?, ?, ?, ?)', [$userId, (int) $values['facility_id'], $values['booking_date'], $values['start_time'], $values['end_time'], $quantity, $values['purpose']]);
            flash('success', 'Your booking request has been sent for approval.');
            redirect('student/bookings.php');
        }
    }
}
$pageTitle = 'Facility and equipment booking';
$activePage = 'dashboard';
$studentSection = 'bookings';
$facilities = fetch_all("SELECT * FROM facilities ORDER BY type, name");
$bookings = fetch_all('SELECT b.*, f.name, f.type, f.location FROM bookings b JOIN facilities f ON f.id = b.facility_id WHERE b.user_id = ? ORDER BY b.booking_date DESC, b.start_time DESC', [$userId]);
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_nav.php';
?>
<div class="container dashboard-head"><span class="section-kicker">Book your space</span>
    <h1>Facilities and equipment
    </h1><p>Send a request to the sports office and follow its approval status here.</p>
</div>
<div class="container dashboard-content">
    <div class="row g-4">
        <div class="col-lg-5">
            <section class="content-panel">
                <h2 class="mb-4">New booking request
                </h2><?php if ($errors): ?>                <div class="alert alert-danger">
                    <ul class="mb-0"><?php foreach ($errors as $error): ?>                        <li><?= e($error) ?>                        </li><?php endforeach; ?>                    </ul>
                </div><?php endif; ?>                <form method="post" class="needs-validation" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="create">
                    <div class="mb-3"><label class="form-label" for="facility_id">Facility or equipment</label><select class="form-select" id="facility_id" name="facility_id" required><option value="">Select an item</option><?php foreach ($facilities as $facility): ?><option value="<?= $facility['id'] ?>" <?= $values['facility_id'] == $facility['id'] ? 'selected' : '' ?> <?= $facility['status'] !== 'active' ? 'disabled' : '' ?>><?= e($facility['name']) ?> — <?= e($facility['type']) ?> (<?= $facility['total_units'] ?> unit<?= $facility['total_units'] > 1 ? 's' : '' ?>)<?= $facility['status'] !== 'active' ? ' — maintenance' : '' ?></option><?php endforeach; ?></select>
                        <div class="invalid-feedback">Choose an available item.
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label" for="booking_date">Date</label><input class="form-control" type="date" id="booking_date" name="booking_date" value="<?= e($values['booking_date']) ?>" data-future-date required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="form-label" for="start_time">From</label><input class="form-control" type="time" id="start_time" name="start_time" value="<?= e($values['start_time']) ?>" required>
                        </div>
                        <div class="col-6 mb-3"><label class="form-label" for="end_time">Until</label><input class="form-control" type="time" id="end_time" name="end_time" value="<?= e($values['end_time']) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3"><label class="form-label" for="quantity">Quantity</label><input class="form-control" type="number" id="quantity" name="quantity" value="<?= e($values['quantity']) ?>" min="1" required>
                        <div class="form-text">Use 1 for a facility booking.
                        </div>
                    </div>
                    <div class="mb-4"><label class="form-label" for="purpose">Purpose</label><textarea class="form-control" id="purpose" name="purpose" minlength="3" maxlength="255" required><?= e($values['purpose']) ?></textarea>
                    </div>
            <button class="btn btn-sport w-100" type="submit">Send booking request</button>
                </form>
            </section>
        </div>
        <div class="col-lg-7">
            <section class="content-panel">
                <h2 class="mb-4">My booking requests
                </h2><?php if ($bookings): ?>                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item
                                </th>
                                <th>Date and time
                                </th>
                                <th>Status
                                </th>
                                <th>Action
                                </th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($bookings as $booking): ?>                            <tr>
                                <td><strong><?= e($booking['name']) ?></strong><br><small><?= e($booking['location']) ?> · Qty <?= $booking['quantity'] ?></small>
                                </td>
                                <td><?= format_date($booking['booking_date']) ?><br><small><?= format_time($booking['start_time']) ?>–<?= format_time($booking['end_time']) ?></small>
                                </td>
                                <td><?= status_badge($booking['status']) ?><?php if ($booking['admin_note']): ?><br><small><?= e($booking['admin_note']) ?></small><?php endif; ?>                                </td>
                                <td><?php if (in_array($booking['status'], ['pending', 'approved'])): ?>                                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel"><input type="hidden" name="booking_id" value="<?= $booking['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Cancel this booking request?">Cancel</button>
                                    </form><?php else: ?>—<?php endif; ?>                                </td>
                            </tr><?php endforeach; ?>                        </tbody>
                    </table>
                </div><?php else: ?><p>You have not made any booking requests.</p><?php endif; ?>            </section>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
