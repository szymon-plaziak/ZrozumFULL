<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get filter parameters
$status = $_GET['status'] ?? 'pending';

// Build query
$where = ["t.assigned_to = ?"];
$params = [$user['id']];

if ($status && $status !== 'all') {
    $where[] = "t.status = ?";
    $params[] = $status;
}

$where_clause = 'WHERE ' . implode(' AND ', $where);

$sql = "
    SELECT t.*, s.name as school_name, u.username as created_by_name
    FROM tasks t
    LEFT JOIN schools s ON t.school_id = s.id
    LEFT JOIN users u ON t.created_by = u.id
    $where_clause
    ORDER BY 
        CASE t.status 
            WHEN 'overdue' THEN 1
            WHEN 'pending' THEN 2
            WHEN 'completed' THEN 3
        END,
        t.due_date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zadania - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Moje zadania</h1>
        </div>
        
        <!-- Status Filter -->
        <div class="search-section">
            <div class="status-tabs">
                <a href="?status=pending" class="status-tab <?= $status === 'pending' ? 'active' : '' ?>">
                    Oczekujące
                </a>
                <a href="?status=overdue" class="status-tab <?= $status === 'overdue' ? 'active' : '' ?>">
                    Zaległe
                </a>
                <a href="?status=completed" class="status-tab <?= $status === 'completed' ? 'active' : '' ?>">
                    Ukończone
                </a>
                <a href="?status=all" class="status-tab <?= $status === 'all' ? 'active' : '' ?>">
                    Wszystkie
                </a>
            </div>
        </div>
        
        <!-- Tasks List -->
        <?php if (empty($tasks)): ?>
            <div class="section">
                <p class="no-data">Brak zadań w tej kategorii.</p>
            </div>
        <?php else: ?>
            <div class="results-count">
                Znaleziono: <?= count($tasks) ?> <?= count($tasks) === 1 ? 'zadanie' : 'zadań' ?>
            </div>
            
            <div class="section">
                <div class="tasks-list">
                    <?php foreach ($tasks as $task): ?>
                    <div class="task-item <?= $task['status'] === 'completed' ? 'completed' : '' ?>">
                        <input type="checkbox" 
                               onclick="toggleTaskStatus(<?= $task['id'] ?>)"
                               <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
                        <div class="task-content">
                            <h4><?= htmlspecialchars($task['title']) ?></h4>
                            <?php if ($task['description']): ?>
                            <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>
                            <?php endif; ?>
                            <div class="task-meta">
                                <?php if ($task['school_name']): ?>
                                <a href="school_card.php?id=<?= $task['school_id'] ?>" class="school-label">
                                    <?= htmlspecialchars($task['school_name']) ?>
                                </a>
                                <?php endif; ?>
                                <span class="task-type"><?= htmlspecialchars($task['task_type']) ?></span>
                            </div>
                        </div>
                        <div class="task-date">
                            <?php if ($task['due_date']): ?>
                            <span class="due-date <?= $task['status'] === 'overdue' ? 'overdue' : '' ?>">
                                <?= date('d.m.Y', strtotime($task['due_date'])) ?>
                            </span>
                            <?php endif; ?>
                            <span class="status-badge status-<?= $task['status'] ?>">
                                <?php
                                $statuses = [
                                    'pending' => 'Oczekujące',
                                    'completed' => 'Ukończone',
                                    'overdue' => 'Zaległe'
                                ];
                                echo $statuses[$task['status']] ?? $task['status'];
                                ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
    .status-tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .status-tab {
        padding: 0.75rem 1.5rem;
        text-decoration: none;
        color: var(--secondary-color);
        font-weight: 500;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    
    .status-tab:hover {
        color: var(--text-color);
    }
    
    .status-tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
    }
    
    .task-item.completed {
        opacity: 0.6;
    }
    
    .task-item.completed .task-content h4 {
        text-decoration: line-through;
    }
    
    .task-meta {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }
    
    .task-type {
        padding: 0.25rem 0.5rem;
        background-color: var(--bg-color);
        border-radius: 0.25rem;
        font-size: 0.75rem;
    }
    
    .due-date.overdue {
        background-color: #fee2e2;
        color: #991b1b;
    }
    </style>
    
    <script src="public/js/main.js"></script>
</body>
</html>
