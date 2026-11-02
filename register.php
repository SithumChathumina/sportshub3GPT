<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect(is_admin() ? 'admin/index.php' : 'student/dashboard.php');
}

$pageTitle = 'Create a student account';
$activePage = 'login';
$errors = [];
$values = ['student_id' => '', 'full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $values['student_id'] = trim($_POST['student_id'] ?? '');
    $values['full_name'] = trim($_POST['full_name'] ?? '');
    $values['email'] = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!preg_match('/^[A-Za-z0-9.\-]{4,20}$/', $values['student_id'])) {
        $errors[] = 'Student ID must be 4–20 characters using letters, numbers, dots or dashes.';
    }
    if (strlen($values['full_name']) < 3 || strlen($values['full_name']) > 100) {
        $errors[] = 'Enter your full name (3–100 characters).';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must be at least 8 characters and include a letter and a number.';
    }
    if ($password !== $confirm) {
        $errors[] = 'The two passwords do not match.';
    }

    if (!$errors) {
        try {
            run_query('INSERT INTO users (student_id, full_name, email, password_hash) VALUES (?, ?, ?, ?)', [
                $values['student_id'], $values['full_name'], $values['email'], password_hash($password, PASSWORD_DEFAULT),
            ]);
            flash('success', 'Your account is ready. You can now log in.');
            redirect('login.php');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'That student ID or email is already registered.';
            } else {
                throw $exception;
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<section class="auth-section">
    <div class="container">
        <div class="auth-layout">
            <div class="auth-intro">
                <span class="hero-kicker">Join the community</span>
                <h1>Your next team is waiting.
                </h1>
                <p>One student account gives you access to club memberships, event registration, training schedules and facility bookings.</p>
            </div>
            <div class="auth-form">
                <h2 class="mb-4">Student registration
                </h2>
                <?php if ($errors): ?>                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0"><?php foreach ($errors as $error): ?>                        <li><?= e($error) ?>                        </li><?php endforeach; ?>                    </ul>
                </div><?php endif; ?>
                <form method="post" class="needs-validation" novalidate>
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="student_id" class="form-label">NSBM student ID</label>
                        <input type="text" class="form-control" id="student_id" name="student_id" value="<?= e($values['student_id']) ?>" pattern="[A-Za-z0-9.\-]{4,20}" required>
                        <div class="invalid-feedback">Use 4–20 letters, numbers, dots or dashes.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?= e($values['full_name']) ?>" minlength="3" maxlength="100" autocomplete="name" required>
                        <div class="invalid-feedback">Enter your full name.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= e($values['email']) ?>" autocomplete="email" required>
                        <div class="invalid-feedback">Enter a valid email address.
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" minlength="8" autocomplete="new-password" required>
                            <div class="form-text">At least 8 characters, with a letter and number.
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label">Confirm password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" autocomplete="new-password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sport w-100">Create my account</button>
                </form>
                <p class="mt-4 mb-0">Already registered? <a href="<?= e(url('login.php')) ?>">Log in</a>.</p>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
