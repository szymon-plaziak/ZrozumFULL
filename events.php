<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get filter parameters
$event_type = $_GET['type'] ?? '';
$date_from = $_GET['date_from'] ?? date('Y-m-d');
$date_to = $_GET['date_to'] ?? date('Y-m-d', strtotime('+3 months'));

// Build query
$where = ["e.event_date BETWEEN ? AND ?"];
$params = [$date_from, $date_to];

if ($event_type) {
    $where[] = "e.event_type = ?";
    $params[] = $event_type;
}

if ($user['role'] !== 'admin') {
    $where[] = "sa.user_id = ?";
    $params[] = $user['id'];
}

$where_clause = 'WHERE ' . implode(' AND ', $where);

if ($user['role'] === 'admin') {
    $sql = "
        SELECT e.*, s.name as school_name, s.city
        FROM events e
        JOIN schools s ON e.school_id = s.id
        $where_clause
        ORDER BY e.event_date ASC, e.event_time ASC
    ";
} else {
    $sql = "
        SELECT e.*, s.name as school_name, s.city
        FROM events e
        JOIN schools s ON e.school_id = s.id
        JOIN school_assignments sa ON s.id = sa.school_id
        $where_clause
        ORDER BY e.event_date ASC, e.event_time ASC
    ";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wydarzenia - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Wydarzenia</h1>
        </div>
        
        <!-- Filters -->
        <div class="search-section">
            <form method="GET" action="events.php" class="search-form">
                <select name="type" class="filter-select">
                    <option value="">Wszystkie typy</option>
                    <option value="school_year_start" <?= $event_type === 'school_year_start' ? 'selected' : '' ?>>
                        Rozpoczęcie roku szkolnego
                    </option>
                    <option value="meeting" <?= $event_type === 'meeting' ? 'selected' : '' ?>>
                        Zebranie
                    </option>
                    <option value="demo" <?= $event_type === 'demo' ? 'selected' : '' ?>>
                        Pokaz
                    </option>
                    <option value="other" <?= $event_type === 'other' ? 'selected' : '' ?>>
                        Inne
                    </option>
                </select>
                
                <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>" 
                       class="filter-select">
                
                <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>" 
                       class="filter-select">
                
                <button type="submit" class="btn btn-primary">Filtruj</button>
            </form>
        </div>
        
        <!-- Events List -->
        <?php if (empty($events)): ?>
            <div class="section">
                <p class="no-data">Brak wydarzeń w wybranym okresie.</p>
            </div>
        <?php else: ?>
            <div class="results-count">
                Znaleziono: <?= count($events) ?> <?= count($events) === 1 ? 'wydarzenie' : 'wydarzeń' ?>
            </div>
            
            <div class="section">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Godzina</th>
                            <th>Typ</th>
                            <th>Tytuł</th>
                            <th>Szkoła</th>
                            <th>Miasto</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($event['event_date'])) ?></td>
                            <td><?= $event['event_time'] ? date('H:i', strtotime($event['event_time'])) : '-' ?></td>
                            <td>
                                <span class="event-type-badge event-type-<?= $event['event_type'] ?>">
                                    <?php
                                    $types = [
                                        'school_year_start' => 'Rok szkolny',
                                        'meeting' => 'Zebranie',
                                        'demo' => 'Pokaz',
                                        'other' => 'Inne'
                                    ];
                                    echo $types[$event['event_type']] ?? $event['event_type'];
                                    ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                            <td>
                                <a href="school_card.php?id=<?= $event['school_id'] ?>">
                                    <?= htmlspecialchars($event['school_name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($event['city'] ?? '-') ?></td>
                            <td>
                                <a href="school_card.php?id=<?= $event['school_id'] ?>" 
                                   class="btn btn-small btn-secondary">Zobacz szkołę</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .event-type-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .event-type-school_year_start {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .event-type-meeting {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .event-type-demo {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .event-type-other {
        background-color: #f3f4f6;
        color: #374151;
    }
    </style>
    
    <script src="public/js/main.js"></script>
</body>
</html>
