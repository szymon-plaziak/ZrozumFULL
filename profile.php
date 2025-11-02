<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Get user statistics
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM school_assignments WHERE user_id = ?
");
$stmt->execute([$user['id']]);
$school_count = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'completed'
");
$stmt->execute([$user['id']]);
$completed_tasks = $stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM tasks WHERE assigned_to = ? AND status = 'pending'
");
$stmt->execute([$user['id']]);
$pending_tasks = $stmt->fetchColumn();

// Get recent activity
$stmt = $pdo->prepare("
    SELECT ch.*, s.name as school_name
    FROM change_history ch
    LEFT JOIN school_details sd ON ch.record_id = sd.id AND ch.table_name = 'school_details'
    LEFT JOIN schools s ON sd.school_id = s.id
    WHERE ch.changed_by = ?
    ORDER BY ch.changed_at DESC
    LIMIT 20
");
$stmt->execute([$user['id']]);
$recent_changes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Mój profil</h1>
            <a href="settings.php" class="btn btn-secondary">Ustawienia</a>
        </div>
        
        <div class="profile-grid">
            <!-- User Info Card -->
            <div class="section">
                <h2>Informacje</h2>
                <div class="profile-info">
                    <div class="profile-avatar">
                        <div class="avatar-placeholder">
                            <?= strtoupper(substr($user['username'], 0, 2)) ?>
                        </div>
                    </div>
                    <div class="profile-details">
                        <h3><?= htmlspecialchars($user['username']) ?></h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                        <span class="role-badge"><?= htmlspecialchars($user['role']) ?></span>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-label">Przypisane szkoły</div>
                        <div class="stat-value"><?= $school_count ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Ukończone zadania</div>
                        <div class="stat-value"><?= $completed_tasks ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Zadania oczekujące</div>
                        <div class="stat-value"><?= $pending_tasks ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="section">
                <h2>Ostatnia aktywność</h2>
                <?php if (empty($recent_changes)): ?>
                    <p class="no-data">Brak aktywności.</p>
                <?php else: ?>
                    <div class="activity-list">
                        <?php foreach ($recent_changes as $change): ?>
                        <div class="activity-item">
                            <div class="activity-icon">✏️</div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    Zmodyfikowano pole: <strong><?= htmlspecialchars($change['field_name']) ?></strong>
                                </div>
                                <?php if ($change['school_name']): ?>
                                <div class="activity-meta">
                                    <?= htmlspecialchars($change['school_name']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="activity-date">
                                    <?= date('d.m.Y H:i', strtotime($change['changed_at'])) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <style>
    .profile-grid {
        display: grid;
        gap: 2rem;
    }
    
    .profile-info {
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .profile-avatar {
        flex-shrink: 0;
    }
    
    .avatar-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: bold;
    }
    
    .profile-details h3 {
        margin-bottom: 0.5rem;
    }
    
    .profile-details p {
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }
    
    .role-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background-color: var(--primary-color);
        color: white;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 500;
    }
    
    .profile-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }
    
    .stat-item {
        text-align: center;
        padding: 1rem;
        background-color: var(--bg-color);
        border-radius: 0.375rem;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: var(--primary-color);
    }
    
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .activity-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background-color: var(--bg-color);
        border-radius: 0.375rem;
    }
    
    .activity-icon {
        font-size: 1.5rem;
    }
    
    .activity-content {
        flex: 1;
    }
    
    .activity-title {
        margin-bottom: 0.25rem;
    }
    
    .activity-meta {
        font-size: 0.875rem;
        color: var(--secondary-color);
        margin-bottom: 0.25rem;
    }
    
    .activity-date {
        font-size: 0.75rem;
        color: var(--secondary-color);
    }
    
    @media (max-width: 768px) {
        .profile-info {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-stats {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <script src="public/js/main.js"></script>
</body>
</html>
