<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/appointment-handler.php';

function admin_is_authenticated(): bool
{
    return !empty($_SESSION['admin_id']);
}

function admin_require_auth(): void
{
    if (!admin_is_authenticated()) {
        header('Location: admin-login');
        exit;
    }
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf'];
}

function admin_verify_csrf(): bool
{
    return hash_equals((string) ($_SESSION['admin_csrf'] ?? ''), (string) ($_POST['csrf_token'] ?? ''));
}

function admin_login(string $email, string $password): bool
{
    $statement = appointment_database()->prepare('SELECT id, email, password_hash FROM admin_users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => strtolower(trim($email))]);
    $admin = $statement->fetch();
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    admin_csrf_token();
    return true;
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
