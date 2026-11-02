<?php
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'University sports, all in one place';
$activePage = 'home';
$clubCount = (int) fetch_one("SELECT COUNT(*) AS total FROM clubs WHERE status = 'active'")['total'];
$memberCount = (int) fetch_one("SELECT COUNT(*) AS total FROM memberships WHERE status = 'approved'")['total'];
$nextMatch = fetch_one("SELECT * FROM matches WHERE status = 'scheduled' AND match_datetime >= NOW() ORDER BY match_datetime LIMIT 1");
$nextTournament = fetch_one("SELECT * FROM tournaments WHERE status != 'completed' AND end_date >= CURDATE() ORDER BY start_date LIMIT 1");
$featuredClubs = fetch_all("SELECT * FROM clubs WHERE status = 'active' ORDER BY id LIMIT 3");

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="hero-grid">
            <div>
                <span class="hero-kicker">Your campus. Your game.</span>
                <h1 class="hero-title">Find your team.<br><span>Play your part.</span>
                </h1>
                <p class="hero-copy">Join an NSBM sports club, keep up with training, enter tournaments and book the facilities you need — without the paperwork.</p>
                <div class="hero-actions">
                    <a class="btn btn-highlight btn-lg" href="<?= e(url('clubs.php')) ?>">Explore sports clubs</a>
                    <a class="text-link" href="<?= e(url('fixtures.php')) ?>">See upcoming fixtures →</a>
                </div>
            </div>
            <figure class="hero-visual">
                <svg viewBox="0 0 520 400" role="img" aria-labelledby="field-title field-desc">
                    <title id="field-title">University sports field illustration</title>
                    <desc id="field-desc">A green sports field with two players and a ball.</desc>
                    <rect x="18" y="18" width="484" height="340" rx="28" fill="#071b22" stroke="#34534c" stroke-width="2"/>
                    <path d="M56 54h408v268H56z" fill="#009147"/>
                    <path d="M260 54v268M56 188h408" stroke="#f4f7f5" stroke-opacity=".65" stroke-width="3"/>
                    <circle cx="260" cy="188" r="48" fill="none" stroke="#f4f7f5" stroke-opacity=".65" stroke-width="3"/>
                    <path d="M56 126h68v124H56M464 126h-68v124h68" fill="none" stroke="#f4f7f5" stroke-opacity=".65" stroke-width="3"/>
                    <circle cx="177" cy="134" r="18" fill="#f6dd38"/>
                    <path d="m165 158-18 64 28 4 22-52-14-16M155 178l-32 28M190 172l34 20M153 224l-24 48M176 224l22 48" fill="none" stroke="#f6dd38" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="354" cy="206" r="18" fill="#f4f7f5"/>
                    <path d="m342 230-22 58 28 8 24-54-12-16M330 248l-34 10M365 244l32 22M326 290l-28 38M350 292l30 34" fill="none" stroke="#f4f7f5" stroke-width="13" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="260" cy="240" r="17" fill="#f4f7f5" stroke="#000312" stroke-width="3"/>
                    <path d="m260 228 8 7-3 10h-11l-4-10 10-7Z" fill="#000312"/>
                    <path d="M0 386h520" stroke="#f6dd38" stroke-width="4"/>
                    <path d="M384 10v32M368 26h32" stroke="#f6dd38" stroke-width="4"/>
                </svg>
                <figcaption>Made for every level — from first practice to finals.</figcaption>
            </figure>
        </div>
    </div>
    <div class="ticker" aria-hidden="true">
        <div class="ticker-track">
            <span>CRICKET · FOOTBALL · BASKETBALL · BADMINTON · RUGBY · ATHLETICS ·&nbsp;</span>
            <span>CRICKET · FOOTBALL · BASKETBALL · BADMINTON · RUGBY · ATHLETICS ·&nbsp;</span>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="whats-on-title">
    <div class="container">
        <div class="section-heading">
            <div><span class="section-kicker">This semester</span>
                <h2 id="whats-on-title">What’s on at SportsHub
                </h2>
            </div>
            <p>Live information from the club database, not another noticeboard.</p>
        </div>
        <div class="bento-grid">
            <article class="info-tile info-tile-dark bento-main">
                <span class="section-kicker">Next fixture</span>
                <?php if ($nextMatch): ?>
            <h3><?= e($nextMatch['home_team']) ?><br>vs <?= e($nextMatch['away_team']) ?>            </h3>
                    <p><?= e($nextMatch['sport']) ?> · <?= e($nextMatch['venue']) ?></p>
                    <span class="big-number"><?= format_date($nextMatch['match_datetime'], 'd M') ?></span>
                    <p><?= format_time($nextMatch['match_datetime']) ?></p>
                    <a class="btn btn-highlight" href="<?= e(url('fixtures.php')) ?>">View all fixtures</a>
                <?php else: ?>
            <h3>No fixtures scheduled yet
            </h3><p>Check back soon for the next game.</p>
                <?php endif; ?>
            </article>
            <article class="info-tile info-tile-green">
                <span class="big-number"><?= $clubCount ?></span>
            <h3>Active sports clubs
            </h3>
                <p><?= $memberCount ?> approved memberships and room for new players.</p>
                <a class="link-arrow" href="<?= e(url('clubs.php')) ?>">Find your club</a>
            </article>
            <article class="info-tile">
                <span class="section-kicker">Next tournament</span>
                <?php if ($nextTournament): ?>
            <h3><?= e($nextTournament['name']) ?>            </h3>
                    <p><?= format_date($nextTournament['start_date']) ?> · <?= e($nextTournament['venue']) ?></p>
                    <a class="link-arrow" href="<?= e(url('student/registrations.php')) ?>">Register to take part</a>
                <?php else: ?>
            <h3>New tournaments coming soon
            </h3>
                <?php endif; ?>
            </article>
        </div>
    </div>
</section>

<section class="section pt-0" aria-labelledby="featured-clubs-title">
    <div class="container">
        <div class="section-heading">
            <div><span class="section-kicker">Pick your sport</span>
                <h2 id="featured-clubs-title">Clubs at NSBM
                </h2>
            </div>
            <a class="link-arrow" href="<?= e(url('clubs.php')) ?>">Browse every club</a>
        </div>
        <div class="club-grid">
            <?php foreach ($featuredClubs as $index => $club): ?>
                <a class="club-card" href="<?= e(url('club.php?id=' . $club['id'])) ?>">
                    <span class="club-number">CLUB <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <h3><?= e($club['name']) ?>            </h3>
                    <p><?= e(substr($club['description'], 0, 110)) ?>…</p>
            <div class="club-meta"><span><?= e($club['meeting_day']) ?>s</span><span><?= e($club['location']) ?></span>
            </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section-dark" aria-labelledby="how-title">
    <div class="container">
        <div class="section-heading">
            <div><span class="section-kicker">Get started</span>
                <h2 id="how-title">From sign-up to game day
                </h2>
            </div>
        </div>
        <div class="steps">
            <article class="step">
            <h3>Create your account
            </h3><p>Use your NSBM student ID and email to register securely.</p></article>
            <article class="step">
            <h3>Join a club
            </h3><p>Send a membership request and the sports admin will review it.</p></article>
            <article class="step">
            <h3>Show up and play
            </h3><p>Follow your schedule, enter events and track your participation.</p></article>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
