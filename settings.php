<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'change_password':
                $current_password = $_POST['current_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($current_password) || empty($new_password)) {
                    $error = 'Wszystkie pola są wymagane.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Nowe hasło musi mieć co najmniej 6 znaków.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Nowe hasła nie pasują do siebie.';
                } else {
                    $pdo = getDBConnection();
                    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                    $stmt->execute([$user['id']]);
                    $stored_hash = $stmt->fetchColumn();
                    
                    if (password_verify($current_password, $stored_hash)) {
                        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$new_hash, $user['id']]);
                        $message = 'Hasło zostało zmienione.';
                    } else {
                        $error = 'Nieprawidłowe obecne hasło.';
                    }
                }
                break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ustawienia - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Ustawienia</h1>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="settings-grid">
            <!-- Change Password -->
            <div class="section">
                <h2>Zmiana hasła</h2>
                <form method="POST" action="settings.php">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Obecne hasło</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nowe hasło</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small>Minimum 6 znaków</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Potwierdź nowe hasło</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Zmień hasło</button>
                </form>
            </div>
            
            <!-- User Information -->
            <div class="section">
                <h2>Informacje o koncie</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nazwa użytkownika:</label>
                        <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <label>Email:</label>
                        <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <label>Rola:</label>
                        <div class="info-value"><?= htmlspecialchars($user['role']) ?></div>
                    </div>
                    
                    <div class="info-item">
                        <label>Data utworzenia:</label>
                        <div class="info-value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .settings-grid {
        display: grid;
        gap: 2rem;
        max-width: 800px;
    }
    </style>
    
    <script src="public/js/main.js"></script>
</body>
</html>
