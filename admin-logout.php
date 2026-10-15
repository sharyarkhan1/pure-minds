<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/admin-auth.php';
admin_logout();
header('Location: admin-login');
exit;
