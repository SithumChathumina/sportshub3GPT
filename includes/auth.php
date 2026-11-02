<?php
function current_user()
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    // Load the account so role changes take effect on the next request.
    $user = fetch_one('SELECT id, student_id, full_name, email, role FROM users WHERE id = ?', [$_SESSION['user_id']]);
    if (!$user) {
        session_destroy();
        return null;
    }
    return $user;
}

function is_logged_in()
{
    return current_user() !== null;
}

function is_admin()
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        flash('warning', 'Please log in to continue.');
        redirect('login.php');
    }
}

function require_student()
{
    require_login();
    if (is_admin()) {
        redirect('admin/index.php');
    }
}

function require_admin()
{
    require_login();
    if (!is_admin()) {
        flash('danger', 'You do not have permission to access the admin panel.');
        redirect('student/dashboard.php');
    }
}
