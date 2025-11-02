<?php
if (!isset($user)) {
    $user = getCurrentUser();
}
?>
<header class="main-header">
    <div class="header-container">
        <div class="header-left">
            <a href="dashboard.php" class="logo">
                <h1>Zrozum</h1>
                <span>System Kart Szkół</span>
            </a>
        </div>
        
        <nav class="main-nav">
            <a href="dashboard.php" class="nav-link">Panel główny</a>
            <a href="schools.php" class="nav-link">Szkoły</a>
            <a href="events.php" class="nav-link">Wydarzenia</a>
            <a href="tasks.php" class="nav-link">Zadania</a>
            <a href="export.php" class="nav-link">Eksport</a>
            <?php if ($user['role'] === 'admin'): ?>
            <a href="admin.php" class="nav-link">Admin</a>
            <?php endif; ?>
        </nav>
        
        <div class="header-right">
            <div class="user-menu">
                <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                <div class="user-dropdown">
                    <a href="profile.php">Profil</a>
                    <a href="settings.php">Ustawienia</a>
                    <a href="logout.php">Wyloguj</a>
                </div>
            </div>
        </div>
    </div>
</header>
