<?php $studentSection = $studentSection ?? ''; ?>
<div class="dashboard-nav-wrap">
    <div class="container">
        <nav class="dashboard-nav" aria-label="Student dashboard">
            <a class="<?= active_class('dashboard', $studentSection) ?>" href="<?= e(url('student/dashboard.php')) ?>">Overview</a>
            <a class="<?= active_class('calendar', $studentSection) ?>" href="<?= e(url('student/calendar.php')) ?>">My schedule</a>
            <a class="<?= active_class('registrations', $studentSection) ?>" href="<?= e(url('student/registrations.php')) ?>">Registrations</a>
            <a class="<?= active_class('bookings', $studentSection) ?>" href="<?= e(url('student/bookings.php')) ?>">Bookings</a>
            <a class="<?= active_class('history', $studentSection) ?>" href="<?= e(url('student/history.php')) ?>">My history</a>
        </nav>
    </div>
</div>
