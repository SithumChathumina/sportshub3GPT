<?php
function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function url($path = '')
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

function asset($path)
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function fetch_all($sql, $params = [])
{
    global $connection;
    // Placeholders keep submitted values separate from the SQL command.
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function fetch_one($sql, $params = [])
{
    global $connection;
    // Placeholders keep submitted values separate from the SQL command.
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    return $statement->fetch();
}

function run_query($sql, $params = [])
{
    global $connection;
    // Placeholders keep submitted values separate from the SQL command.
    $statement = $connection->prepare($sql);
    $statement->execute($params);
    return $statement;
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Your form session expired. Please go back and try again.');
    }
}

function flash($type, $message)
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes()
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function format_date($date, $format = 'd M Y')
{
    return $date ? date($format, strtotime($date)) : 'Not set';
}

function format_time($time)
{
    return $time ? date('g:i A', strtotime($time)) : 'Not set';
}

function status_badge($status)
{
    $classes = [
        'approved' => 'success', 'active' => 'success', 'completed' => 'success',
        'registered' => 'success', 'ongoing' => 'info',
        'pending' => 'warning', 'upcoming' => 'warning', 'scheduled' => 'info',
        'rejected' => 'danger', 'cancelled' => 'danger', 'inactive' => 'secondary',
        'withdrawn' => 'secondary', 'maintenance' => 'danger',
    ];
    $class = $classes[$status] ?? 'secondary';
    return '<span class="badge text-bg-' . $class . '">' . e(ucfirst($status)) . '</span>';
}

function active_class($page, $activePage)
{
    return $page === $activePage ? 'active' : '';
}

function client_validation()
{
    return 'needs-validation';
}
