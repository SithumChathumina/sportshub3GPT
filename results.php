<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Results and standings';
$activePage = 'results';
$sport = trim($_GET['sport'] ?? '');
$params = [];
$where = "status = 'completed'";
if ($sport !== '') { $where .= ' AND sport = ?'; $params[] = $sport; }
$results = fetch_all("SELECT * FROM matches WHERE $where ORDER BY match_datetime DESC", $params);
$sports = fetch_all('SELECT DISTINCT sport FROM matches ORDER BY sport');

$standings = [];
foreach ($results as $match) {
    foreach (['home', 'away'] as $side) {
        $team = $match[$side . '_team'];
        if (!isset($standings[$team])) $standings[$team] = ['team' => $team, 'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points' => 0];
        $own = (int) $match[$side . '_score'];
        $otherSide = $side === 'home' ? 'away' : 'home';
        $other = (int) $match[$otherSide . '_score'];
        $standings[$team]['played']++;
        if ($own > $other) { $standings[$team]['won']++; $standings[$team]['points'] += 3; }
        elseif ($own === $other) { $standings[$team]['drawn']++; $standings[$team]['points']++; }
        else { $standings[$team]['lost']++; }
    }
}
// Highest points first, then wins; use team name to break a remaining tie.
function compare_standings($first, $second)
{
    if ($first['points'] != $second['points']) {
        return $second['points'] - $first['points'];
    }
    if ($first['won'] != $second['won']) {
        return $second['won'] - $first['won'];
    }
    return strcmp($first['team'], $second['team']);
}
usort($standings, 'compare_standings');

include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container"><span class="hero-kicker">After the final whistle</span>
        <h1>Results and standings
        </h1><p>Recent scores and a simple three-points-for-a-win table calculated from completed matches.</p>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <form class="filter-bar" method="get">
            <div><label for="sport" class="visually-hidden">Sport</label><select class="form-select" id="sport" name="sport"><option value="">All sports</option><?php foreach ($sports as $row): ?><option value="<?= e($row['sport']) ?>" <?= $sport === $row['sport'] ? 'selected' : '' ?>><?= e($row['sport']) ?></option><?php endforeach; ?></select>
            </div>
            <div>
            </div><button class="btn btn-sport" type="submit">Show results</button>
        </form>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="content-panel">
                    <h2 class="mb-4">Latest results
                    </h2><?php if ($results): ?>                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date
                                    </th>
                                    <th>Sport
                                    </th>
                                    <th>Match
                                    </th>
                                    <th>Score
                                    </th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($results as $match): ?>                                <tr>
                                    <td><?= format_date($match['match_datetime']) ?>                                    </td>
                                    <td><?= e($match['sport']) ?>                                    </td>
                                    <td><?= e($match['home_team']) ?><br><?= e($match['away_team']) ?>                                    </td>
                                    <td class="score"><?= $match['home_score'] ?> – <?= $match['away_score'] ?>                                    </td>
                                </tr><?php endforeach; ?>                            </tbody>
                        </table>
                    </div><?php else: ?><p>No completed matches match this filter.</p><?php endif; ?>                </div>
            </div>
            <div class="col-lg-5">
                <div class="content-panel">
                    <h2 class="mb-4">Standings
                    </h2><?php if ($standings): ?>                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Team
                                    </th>
                                    <th>P
                                    </th>
                                    <th>W
                                    </th>
                                    <th>D
                                    </th>
                                    <th>L
                                    </th>
                                    <th>Pts
                                    </th>
                                </tr>
                            </thead>
                            <tbody><?php foreach ($standings as $row): ?>                                <tr>
                                    <td><strong><?= e($row['team']) ?></strong>
                                    </td>
                                    <td><?= $row['played'] ?>                                    </td>
                                    <td><?= $row['won'] ?>                                    </td>
                                    <td><?= $row['drawn'] ?>                                    </td>
                                    <td><?= $row['lost'] ?>                                    </td>
                                    <td><strong><?= $row['points'] ?></strong>
                                    </td>
                                </tr><?php endforeach; ?>                            </tbody>
                        </table>
                    </div><p class="required-note mt-3 mb-0">This basic standings table is intended for league-style comparison within one sport.</p><?php else: ?><p>Standings appear after completed results are recorded.</p><?php endif; ?>                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
