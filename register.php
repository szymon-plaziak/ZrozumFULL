<?php
require_once 'config/database.php';
require_once 'config/session.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Wszystkie pola są wymagane.';
    } elseif (strlen($username) < 3) {
        $error = 'Nazwa użytkownika musi mieć co najmniej 3 znaki.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nieprawidłowy adres email.';
    } elseif (strlen($password) < 6) {
        $error = 'Hasło musi mieć co najmniej 6 znaków.';
    } elseif ($password !== $password_confirm) {
        $error = 'Hasła nie pasują do siebie.';
    } else {
        $pdo = getDBConnection();
        
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = 'Nazwa użytkownika lub email już istnieje.';
        } else {
            // Create user
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'worker')");
            
            try {
                $stmt->execute([$username, $email, $password_hash]);
                $success = 'Konto zostało utworzone. Możesz się teraz zalogować.';
            } catch (PDOException $e) {
                $error = 'Wystąpił błąd podczas rejestracji. Spróbuj ponownie.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - System Kart Szkół Zrozum</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Rejestracja</h1>
            <h2>System Kart Szkół</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="register.php">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="username">Nazwa użytkownika</label>
                    <input type="text" id="username" name="username" required autofocus 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Hasło</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimum 6 znaków</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Potwierdź hasło</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Zarejestruj się</button>
            </form>
            
            <p class="register-link">
                Masz już konto? <a href="login.php">Zaloguj się</a>
            </p>
        </div>
    </div>
</body>
</html>
