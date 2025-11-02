<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get user's assigned schools
$stmt = $pdo->prepare("
    SELECT s.*, sa.assigned_at,
           (SELECT COUNT(*) FROM events WHERE school_id = s.id AND event_date >= CURDATE()) as upcoming_events,
           (SELECT COUNT(*) FROM tasks WHERE school_id = s.id AND assigned_to = ? AND status = 'pending') as pending_tasks
    FROM schools s
    JOIN school_assignments sa ON s.id = sa.school_id
    WHERE sa.user_id = ?
    ORDER BY s.name
");
$stmt->execute([$user['id'], $user['id']]);
$schools = $stmt->fetchAll();

// Get pending tasks for this user
$stmt = $pdo->prepare("
    SELECT t.*, s.name as school_name
    FROM tasks t
    LEFT JOIN schools s ON t.school_id = s.id
    WHERE t.assigned_to = ? AND t.status = 'pending'
    ORDER BY t.due_date ASC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$tasks = $stmt->fetchAll();

// Get upcoming events
$stmt = $pdo->prepare("
    SELECT e.*, s.name as school_name
    FROM events e
    JOIN schools s ON e.school_id = s.id
    JOIN school_assignments sa ON s.id = sa.school_id
    WHERE sa.user_id = ? AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC, e.event_time ASC
    LIMIT 10
");
$stmt->execute([$user['id']]);
$events = $stmt->fetchAll();

// Get contracts expiring soon (next 60 days)
$stmt = $pdo->prepare("
    SELECT c.*, s.name as school_name
    FROM contracts c
    JOIN schools s ON c.school_id = s.id
    JOIN school_assignments sa ON s.id = sa.school_id
    WHERE sa.user_id = ? AND c.status = 'active' 
          AND c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)
    ORDER BY c.end_date ASC
");
$stmt->execute([$user['id']]);
$expiring_contracts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel główny - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Panel główny</h1>
            <p>Witaj, <?= htmlspecialchars($user['username']) ?>!</p>
        </div>
        
        <div class="dashboard-grid">
            <!-- Statistics Cards -->
            <div class="stat-card">
                <h3>Moje szkoły</h3>
                <div class="stat-number"><?= count($schools) ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Zadania do wykonania</h3>
                <div class="stat-number"><?= count($tasks) ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Nadchodzące wydarzenia</h3>
                <div class="stat-number"><?= count($events) ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Wygasające umowy</h3>
                <div class="stat-number"><?= count($expiring_contracts) ?></div>
            </div>
        </div>
        
        <!-- Tasks Section -->
        <?php if (!empty($tasks)): ?>
        <div class="section">
            <h2>Zadania do wykonania</h2>
            <div class="tasks-list">
                <?php foreach ($tasks as $task): ?>
                <div class="task-item">
                    <div class="task-info">
                        <h4><?= htmlspecialchars($task['title']) ?></h4>
                        <p><?= htmlspecialchars($task['description'] ?? '') ?></p>
                        <?php if ($task['school_name']): ?>
                        <span class="school-label"><?= htmlspecialchars($task['school_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="task-date">
                        <?php if ($task['due_date']): ?>
                        <span class="due-date"><?= date('d.m.Y', strtotime($task['due_date'])) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Expiring Contracts -->
        <?php if (!empty($expiring_contracts)): ?>
        <div class="section">
            <h2>Wygasające umowy (najbliższe 60 dni)</h2>
            <div class="contracts-list">
                <?php foreach ($expiring_contracts as $contract): ?>
                <div class="contract-item alert alert-warning">
                    <strong><?= htmlspecialchars($contract['school_name']) ?></strong>
                    <span>Umowa wygasa: <?= date('d.m.Y', strtotime($contract['end_date'])) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Schools List -->
        <div class="section">
            <div class="section-header">
                <h2>Moje szkoły</h2>
                <a href="schools.php" class="btn btn-secondary">Zobacz wszystkie</a>
            </div>
            
            <?php if (empty($schools)): ?>
                <p class="no-data">Nie masz jeszcze przypisanych szkół.</p>
            <?php else: ?>
                <div class="schools-grid">
                    <?php foreach ($schools as $school): ?>
                    <a href="school_card.php?id=<?= $school['id'] ?>" class="school-card">
                        <h3><?= htmlspecialchars($school['name']) ?></h3>
                        <p class="school-location">
                            <?php if ($school['city']): ?>
                                <?= htmlspecialchars($school['city']) ?>
                            <?php endif; ?>
                        </p>
                        <div class="school-stats">
                            <?php if ($school['upcoming_events'] > 0): ?>
                                <span class="badge badge-info"><?= $school['upcoming_events'] ?> wydarzeń</span>
                            <?php endif; ?>
                            <?php if ($school['pending_tasks'] > 0): ?>
                                <span class="badge badge-warning"><?= $school['pending_tasks'] ?> zadań</span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Upcoming Events -->
        <?php if (!empty($events)): ?>
        <div class="section">
            <h2>Nadchodzące wydarzenia</h2>
            <div class="events-list">
                <?php foreach ($events as $event): ?>
                <div class="event-item">
                    <div class="event-date">
                        <span class="day"><?= date('d', strtotime($event['event_date'])) ?></span>
                        <span class="month"><?= date('M', strtotime($event['event_date'])) ?></span>
                    </div>
                    <div class="event-info">
                        <h4><?= htmlspecialchars($event['title']) ?></h4>
                        <p><?= htmlspecialchars($event['school_name']) ?></p>
                        <span class="event-type"><?= htmlspecialchars($event['event_type']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="public/js/main.js"></script>
</body>
</html>
