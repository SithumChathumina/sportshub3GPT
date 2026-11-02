<?php
require_once __DIR__ . '/includes/init.php';
$pageTitle = 'Sports clubs';
$activePage = 'clubs';
$search = trim($_GET['search'] ?? '');
$sport = trim($_GET['sport'] ?? '');
$conditions = ["c.status = 'active'"];
$params = [];
if ($search !== '') {
    $conditions[] = '(c.name LIKE ? OR c.description LIKE ? OR c.coach_name LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}
if ($sport !== '') {
    $conditions[] = 'c.sport = ?';
    $params[] = $sport;
}
$clubs = fetch_all('SELECT c.*, (SELECT COUNT(*) FROM memberships m WHERE m.club_id = c.id AND m.status = \'approved\') AS member_count FROM clubs c WHERE ' . implode(' AND ', $conditions) . ' ORDER BY c.name', $params);
$sports = fetch_all("SELECT DISTINCT sport FROM clubs WHERE status = 'active' ORDER BY sport");
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container"><span class="hero-kicker">Find your team</span>
        <h1>Sports clubs
        </h1><p>From your first training session to your next championship, there is a place for you.</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <form class="filter-bar" method="get" role="search">
            <div><label for="search" class="visually-hidden">Search clubs</label><input class="form-control" type="search" id="search" name="search" value="<?= e($search) ?>" placeholder="Search by club, description or coach">
            </div>
            <div><label for="sport" class="visually-hidden">Filter by sport</label><select class="form-select" id="sport" name="sport"><option value="">All sports</option><?php foreach ($sports as $row): ?><option value="<?= e($row['sport']) ?>" <?= $sport === $row['sport'] ? 'selected' : '' ?>><?= e($row['sport']) ?></option><?php endforeach; ?></select>
            </div>
            <button class="btn btn-sport" type="submit">Search clubs</button>
        </form>
        <?php if ($clubs): ?>
        <div class="club-grid">
                <?php foreach ($clubs as $index => $club): ?>
                    <a class="club-card" href="<?= e(url('club.php?id=' . $club['id'])) ?>">
                        <span class="club-number"><?= e(strtoupper($club['sport'])) ?> · <?= $club['member_count'] ?> MEMBERS</span>
            <h3><?= e($club['name']) ?>            </h3>
                        <p><?= e($club['description']) ?></p>
            <div class="club-meta"><span>Coach: <?= e($club['coach_name']) ?></span><span><?= e($club['meeting_day']) ?>s · <?= e($club['location']) ?></span>
            </div>
                    </a>
                <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <h2>No clubs found
            </h2><p>Try another keyword or clear the sport filter.</p><a class="btn btn-sport" href="<?= e(url('clubs.php')) ?>">Show all clubs</a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
