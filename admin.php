<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();

// Check if user is admin
if ($user['role'] !== 'admin') {
    die('Brak dostępu. Tylko administratorzy mogą przeglądać tę stronę.');
}

$pdo = getDBConnection();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM schools");
$total_schools = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE event_date >= CURDATE()");
$upcoming_events = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'pending'");
$pending_tasks = $stmt->fetchColumn();

// Get recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
$recent_users = $stmt->fetchAll();

// Get recent schools
$stmt = $pdo->query("SELECT * FROM schools ORDER BY created_at DESC LIMIT 10");
$recent_schools = $stmt->fetchAll();

// Get unassigned schools
$stmt = $pdo->query("
    SELECT s.* 
    FROM schools s
    LEFT JOIN school_assignments sa ON s.id = sa.school_id
    WHERE sa.id IS NULL
    ORDER BY s.name
");
$unassigned_schools = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel administratora - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Panel administratora</h1>
        </div>
        
        <!-- Statistics -->
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Użytkownicy</h3>
                <div class="stat-number"><?= $total_users ?></div>
                <a href="#users" class="stat-link">Zobacz wszystkich</a>
            </div>
            
            <div class="stat-card">
                <h3>Szkoły</h3>
                <div class="stat-number"><?= $total_schools ?></div>
                <a href="#schools" class="stat-link">Zobacz wszystkie</a>
            </div>
            
            <div class="stat-card">
                <h3>Nadchodzące wydarzenia</h3>
                <div class="stat-number"><?= $upcoming_events ?></div>
                <a href="events.php" class="stat-link">Zobacz wydarzenia</a>
            </div>
            
            <div class="stat-card">
                <h3>Zadania oczekujące</h3>
                <div class="stat-number"><?= $pending_tasks ?></div>
                <a href="tasks.php" class="stat-link">Zobacz zadania</a>
            </div>
        </div>
        
        <!-- Unassigned Schools Alert -->
        <?php if (!empty($unassigned_schools)): ?>
        <div class="alert alert-warning">
            <strong>Uwaga!</strong> Masz <?= count($unassigned_schools) ?> 
            <?= count($unassigned_schools) === 1 ? 'szkołę' : 'szkół' ?> bez przypisanego pracownika.
            <a href="#unassigned">Zobacz listę</a>
        </div>
        <?php endif; ?>
        
        <!-- Recent Users -->
        <div class="section" id="users">
            <h2>Ostatnio zarejestrowani użytkownicy</h2>
            <?php if (empty($recent_users)): ?>
                <p class="no-data">Brak użytkowników.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa użytkownika</th>
                            <th>Email</th>
                            <th>Rola</th>
                            <th>Data rejestracji</th>
                            <th>Ostatnie logowanie</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="role-badge"><?= $u['role'] ?></span></td>
                            <td><?= date('d.m.Y H:i', strtotime($u['created_at'])) ?></td>
                            <td><?= $u['last_login'] ? date('d.m.Y H:i', strtotime($u['last_login'])) : 'Nigdy' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Recent Schools -->
        <div class="section" id="schools">
            <h2>Ostatnio dodane szkoły</h2>
            <?php if (empty($recent_schools)): ?>
                <p class="no-data">Brak szkół.</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa</th>
                            <th>Miasto</th>
                            <th>Typ</th>
                            <th>Data dodania</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_schools as $s): ?>
                        <tr>
                            <td><?= $s['id'] ?></td>
                            <td>
                                <a href="school_card.php?id=<?= $s['id'] ?>">
                                    <?= htmlspecialchars($s['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($s['city'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['school_type'] ?? '-') ?></td>
                            <td><?= date('d.m.Y', strtotime($s['created_at'])) ?></td>
                            <td>
                                <a href="school_card.php?id=<?= $s['id'] ?>" class="btn btn-small btn-secondary">
                                    Zobacz
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Unassigned Schools -->
        <?php if (!empty($unassigned_schools)): ?>
        <div class="section" id="unassigned">
            <h2>Szkoły bez przypisanego pracownika</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazwa</th>
                        <th>Miasto</th>
                        <th>Data dodania</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unassigned_schools as $s): ?>
                    <tr>
                        <td><?= $s['id'] ?></td>
                        <td>
                            <a href="school_card.php?id=<?= $s['id'] ?>">
                                <?= htmlspecialchars($s['name']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($s['city'] ?? '-') ?></td>
                        <td><?= date('d.m.Y', strtotime($s['created_at'])) ?></td>
                        <td>
                            <button onclick="assignSchool(<?= $s['id'] ?>)" class="btn btn-small btn-primary">
                                Przypisz pracownika
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="section">
            <h2>Szybkie akcje</h2>
            <div class="quick-actions">
                <button onclick="showAddUserForm()" class="btn btn-primary">
                    <span class="icon">👤</span> Dodaj użytkownika
                </button>
                <button onclick="showAddSchoolForm()" class="btn btn-primary">
                    <span class="icon">🏫</span> Dodaj szkołę
                </button>
                <a href="export.php" class="btn btn-secondary">
                    <span class="icon">📊</span> Eksportuj dane
                </a>
                <button onclick="showDatabaseInfo()" class="btn btn-secondary">
                    <span class="icon">🗄️</span> Info o bazie danych
                </button>
            </div>
        </div>
    </div>
    
    <style>
    .stat-link {
        display: block;
        margin-top: 0.5rem;
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .stat-link:hover {
        text-decoration: underline;
    }
    
    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
        background-color: var(--primary-color);
        color: white;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .quick-actions .btn {
        justify-content: center;
    }
    </style>
    
    <script src="public/js/main.js"></script>
    <script>
    function assignSchool(schoolId) {
        showNotification('Funkcja przypisywania szkół w przygotowaniu', 'info');
    }
    
    function showAddUserForm() {
        showNotification('Funkcja dodawania użytkowników w przygotowaniu', 'info');
    }
    
    function showAddSchoolForm() {
        showNotification('Funkcja dodawania szkół w przygotowaniu', 'info');
    }
    
    function showDatabaseInfo() {
        showNotification('Informacje o bazie danych w przygotowaniu', 'info');
    }
    </script>
</body>
</html>
