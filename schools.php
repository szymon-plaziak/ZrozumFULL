<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_city = isset($_GET['city']) ? trim($_GET['city']) : '';

// Build query
$where = [];
$params = [];

if ($user['role'] !== 'admin') {
    $where[] = "sa.user_id = ?";
    $params[] = $user['id'];
}

if ($search) {
    $where[] = "(s.name LIKE ? OR s.city LIKE ? OR s.postal_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter_city) {
    $where[] = "s.city = ?";
    $params[] = $filter_city;
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

if ($user['role'] === 'admin') {
    $sql = "SELECT s.* FROM schools s $where_clause ORDER BY s.name";
} else {
    $sql = "
        SELECT s.*
        FROM schools s
        JOIN school_assignments sa ON s.id = sa.school_id
        $where_clause
        ORDER BY s.name
    ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schools = $stmt->fetchAll();

// Get list of cities for filter
$stmt = $pdo->query("SELECT DISTINCT city FROM schools WHERE city IS NOT NULL ORDER BY city");
$cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista szkół - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Lista szkół</h1>
            <?php if ($user['role'] === 'admin'): ?>
            <a href="school_add.php" class="btn btn-primary">+ Dodaj szkołę</a>
            <?php endif; ?>
        </div>
        
        <!-- Search and Filter -->
        <div class="search-section">
            <form method="GET" action="schools.php" class="search-form">
                <input type="text" name="search" placeholder="Szukaj szkoły..." 
                       value="<?= htmlspecialchars($search) ?>" class="search-input">
                
                <select name="city" class="filter-select">
                    <option value="">Wszystkie miasta</option>
                    <?php foreach ($cities as $city): ?>
                    <option value="<?= htmlspecialchars($city) ?>" 
                            <?= $filter_city === $city ? 'selected' : '' ?>>
                        <?= htmlspecialchars($city) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">Szukaj</button>
                <?php if ($search || $filter_city): ?>
                <a href="schools.php" class="btn btn-secondary">Wyczyść</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Schools List -->
        <?php if (empty($schools)): ?>
            <p class="no-data">Nie znaleziono szkół.</p>
        <?php else: ?>
            <div class="results-count">
                Znaleziono: <?= count($schools) ?> <?= count($schools) === 1 ? 'szkołę' : 'szkół' ?>
            </div>
            
            <table class="data-table schools-table">
                <thead>
                    <tr>
                        <th>Nazwa szkoły</th>
                        <th>Miasto</th>
                        <th>Typ</th>
                        <th>Kontakt</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schools as $school): ?>
                    <tr>
                        <td>
                            <a href="school_card.php?id=<?= $school['id'] ?>" class="school-link">
                                <strong><?= htmlspecialchars($school['name']) ?></strong>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($school['city'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($school['school_type'] ?? '-') ?></td>
                        <td>
                            <?php if ($school['phone']): ?>
                                <div><?= htmlspecialchars($school['phone']) ?></div>
                            <?php endif; ?>
                            <?php if ($school['email']): ?>
                                <div><a href="mailto:<?= htmlspecialchars($school['email']) ?>">
                                    <?= htmlspecialchars($school['email']) ?>
                                </a></div>
                            <?php endif; ?>
                        </td>
                        <td class="actions-cell">
                            <a href="school_card.php?id=<?= $school['id'] ?>" 
                               class="btn btn-small btn-secondary">Zobacz</a>
                            <?php if ($user['role'] === 'admin'): ?>
                            <a href="school_edit.php?id=<?= $school['id'] ?>" 
                               class="btn btn-small btn-primary">Edytuj</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script src="public/js/main.js"></script>
</body>
</html>
