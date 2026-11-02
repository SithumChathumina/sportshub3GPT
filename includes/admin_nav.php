<?php $adminSection = $adminSection ?? ''; ?>
<div class="dashboard-nav-wrap admin-nav-wrap">
    <div class="container">
        <nav class="dashboard-nav" aria-label="Admin panel">
            <a class="<?= active_class('dashboard', $adminSection) ?>" href="<?= e(url('admin/index.php')) ?>">Overview</a>
            <a class="<?= active_class('clubs', $adminSection) ?>" href="<?= e(url('admin/clubs.php')) ?>">Clubs</a>
            <a class="<?= active_class('memberships', $adminSection) ?>" href="<?= e(url('admin/memberships.php')) ?>">Memberships</a>
            <a class="<?= active_class('sessions', $adminSection) ?>" href="<?= e(url('admin/sessions.php')) ?>">Training</a>
            <a class="<?= active_class('tournaments', $adminSection) ?>" href="<?= e(url('admin/tournaments.php')) ?>">Tournaments</a>
            <a class="<?= active_class('matches', $adminSection) ?>" href="<?= e(url('admin/matches.php')) ?>">Matches</a>
            <a class="<?= active_class('results', $adminSection) ?>" href="<?= e(url('admin/results.php')) ?>">Results</a>
            <a class="<?= active_class('facilities', $adminSection) ?>" href="<?= e(url('admin/facilities.php')) ?>">Facilities</a>
            <a class="<?= active_class('bookings', $adminSection) ?>" href="<?= e(url('admin/bookings.php')) ?>">Bookings</a>
            <a class="<?= active_class('reports', $adminSection) ?>" href="<?= e(url('admin/reports.php')) ?>">Reports</a>
        </nav>
    </div>
</div>
