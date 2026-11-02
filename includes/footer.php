</main>
<footer class="site-footer">
<div class="container">
    <div class="row g-4 align-items-start">
        <div class="col-lg-5">
                <a class="footer-brand" href="<?= e(url('index.php')) ?>">NSBM SPORTSHUB</a>
                <p class="footer-copy">One place for university clubs, training, fixtures and the next game.</p>
        </div>
        <div class="col-6 col-lg-3">
            <h2 class="footer-heading">Explore
            </h2>
                <a href="<?= e(url('clubs.php')) ?>">Sports clubs</a>
                <a href="<?= e(url('fixtures.php')) ?>">Upcoming fixtures</a>
                <a href="<?= e(url('results.php')) ?>">Results and standings</a>
        </div>
        <div class="col-6 col-lg-4">
            <h2 class="footer-heading">Students
            </h2>
                <a href="<?= e(url('student/registrations.php')) ?>">Tournament registration</a>
                <a href="<?= e(url('student/bookings.php')) ?>">Facility booking</a>
                <a href="mailto:sportshub@nsbm.lk">sportshub@nsbm.lk</a>
        </div>
    </div>
    <div class="footer-bottom">
            <span>Faculty of Computing · Web and Mobile Application Development</span>
            <span>&copy; <?= date('Y') ?> NSBM SportsHub</span>
    </div>
</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
