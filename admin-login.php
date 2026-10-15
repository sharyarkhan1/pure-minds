<?php
include_once __DIR__ . '/includes/config.php';
include_once __DIR__ . '/includes/admin-auth.php';

if (admin_is_authenticated()) {
    header('Location: admin');
    exit;
}

$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    try {
        if (admin_login($email, $password)) {
            header('Location: admin');
            exit;
        }
        $error = 'The email or password is incorrect.';
    } catch (Throwable $exception) {
        error_log('Admin login failed: ' . $exception->getMessage());
        $error = 'The admin portal is temporarily unavailable.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Pure Minds</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="admin-body">
<main class="admin-login-wrap">
    <section class="admin-login-panel" aria-labelledby="admin-login-title">
        <a href="<?php echo htmlspecialchars($site_url . '/'); ?>" class="admin-brand"><img src="images/logo.png" alt="Pure Minds"></a>
        <p class="admin-eyebrow">STAFF ACCESS</p>
        <h1 id="admin-login-title">Admin login</h1>
        <p class="admin-muted">Manage open appointment times for your practice.</p>
        <?php if ($error !== ''): ?><div class="admin-alert" role="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required autocomplete="username" autofocus>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit" class="btn-wth-bg">Log in</button>
        </form>
    </section>
</main>
</body>
</html>
