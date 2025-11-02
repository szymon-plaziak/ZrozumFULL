<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect to login if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Redirect to dashboard
header('Location: dashboard.php');
exit;
?>
