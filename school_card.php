<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

$school_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$school_id) {
    header('Location: schools.php');
    exit;
}

// Get school data
$stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
$stmt->execute([$school_id]);
$school = $stmt->fetch();

if (!$school) {
    header('Location: schools.php');
    exit;
}

// Check if user has access to this school
$stmt = $pdo->prepare("SELECT COUNT(*) FROM school_assignments WHERE school_id = ? AND user_id = ?");
$stmt->execute([$school_id, $user['id']]);
$has_access = $stmt->fetchColumn() > 0 || $user['role'] === 'admin';

if (!$has_access) {
    die('Brak dostępu do tej szkoły.');
}

// Get school details grouped by section
$stmt = $pdo->prepare("
    SELECT * FROM school_details 
    WHERE school_id = ? 
    ORDER BY section, display_order, field_name
");
$stmt->execute([$school_id]);
$details = $stmt->fetchAll();

$sections = [];
foreach ($details as $detail) {
    $sections[$detail['section']][] = $detail;
}

// Get events for this school
$stmt = $pdo->prepare("
    SELECT * FROM events 
    WHERE school_id = ? 
    ORDER BY event_date DESC, event_time DESC
");
$stmt->execute([$school_id]);
$events = $stmt->fetchAll();

// Get contracts for this school
$stmt = $pdo->prepare("
    SELECT * FROM contracts 
    WHERE school_id = ? 
    ORDER BY end_date DESC
");
$stmt->execute([$school_id]);
$contracts = $stmt->fetchAll();

// Get tasks for this school
$stmt = $pdo->prepare("
    SELECT t.*, u.username as assigned_to_name
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.school_id = ? 
    ORDER BY t.due_date ASC, t.status
");
$stmt->execute([$school_id]);
$tasks = $stmt->fetchAll();

// Get user preferences for visible sections
$stmt = $pdo->prepare("
    SELECT preference_value FROM user_preferences 
    WHERE user_id = ? AND preference_key = 'school_card_visible_sections'
");
$stmt->execute([$user['id']]);
$visible_sections_pref = $stmt->fetchColumn();
$visible_sections = $visible_sections_pref ? json_decode($visible_sections_pref, true) : [];

// Default visible sections if not set
if (empty($visible_sections)) {
    $visible_sections = ['basic_info', 'contact', 'schedule', 'events', 'contracts', 'tasks'];
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($school['name']) ?> - Karta szkoły</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="school-card-header">
            <div class="school-card-title">
                <h1><?= htmlspecialchars($school['name']) ?></h1>
                <div class="school-meta">
                    <span>ID: <?= $school['id'] ?></span>
                    <?php if ($school['school_type']): ?>
                    <span>Typ: <?= htmlspecialchars($school['school_type']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="school-card-actions">
                <button onclick="toggleSectionSettings()" class="btn btn-secondary">
                    <span class="icon">⚙</span> Widok
                </button>
                <button onclick="window.print()" class="btn btn-secondary">
                    <span class="icon">🖨</span> Drukuj
                </button>
                <a href="school_edit.php?id=<?= $school_id ?>" class="btn btn-primary">
                    <span class="icon">✏</span> Edytuj
                </a>
            </div>
        </div>
        
        <!-- Section visibility settings (hidden by default) -->
        <div id="section-settings" class="section-settings" style="display: none;">
            <h3>Wybierz widoczne sekcje:</h3>
            <div class="section-toggles" id="section-toggles">
                <!-- Populated by JavaScript -->
            </div>
        </div>
        
        <!-- Basic Information Section -->
        <div class="school-section" data-section="basic_info" 
             style="<?= in_array('basic_info', $visible_sections) ? '' : 'display:none;' ?>">
            <h2 class="section-title">Podstawowe informacje</h2>
            <div class="info-grid">
                <?php if ($school['address']): ?>
                <div class="info-item">
                    <label>Adres:</label>
                    <div class="info-value"><?= nl2br(htmlspecialchars($school['address'])) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($school['city']): ?>
                <div class="info-item">
                    <label>Miasto:</label>
                    <div class="info-value"><?= htmlspecialchars($school['city']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($school['postal_code']): ?>
                <div class="info-item">
                    <label>Kod pocztowy:</label>
                    <div class="info-value"><?= htmlspecialchars($school['postal_code']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($school['student_count']): ?>
                <div class="info-item">
                    <label>Liczba uczniów:</label>
                    <div class="info-value"><?= htmlspecialchars($school['student_count']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Contact Information Section -->
        <div class="school-section" data-section="contact" 
             style="<?= in_array('contact', $visible_sections) ? '' : 'display:none;' ?>">
            <h2 class="section-title">Kontakt</h2>
            <div class="info-grid">
                <?php if ($school['phone']): ?>
                <div class="info-item">
                    <label>Telefon:</label>
                    <div class="info-value"><?= htmlspecialchars($school['phone']) ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($school['email']): ?>
                <div class="info-item">
                    <label>Email:</label>
                    <div class="info-value">
                        <a href="mailto:<?= htmlspecialchars($school['email']) ?>">
                            <?= htmlspecialchars($school['email']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($school['website']): ?>
                <div class="info-item">
                    <label>Strona WWW:</label>
                    <div class="info-value">
                        <a href="<?= htmlspecialchars($school['website']) ?>" target="_blank">
                            <?= htmlspecialchars($school['website']) ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Custom Sections from school_details -->
        <?php foreach ($sections as $section_name => $section_details): ?>
        <div class="school-section" data-section="<?= htmlspecialchars($section_name) ?>" 
             style="<?= in_array($section_name, $visible_sections) ? '' : 'display:none;' ?>">
            <h2 class="section-title"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $section_name))) ?></h2>
            <div class="info-grid">
                <?php foreach ($section_details as $detail): ?>
                <div class="info-item">
                    <label><?= htmlspecialchars($detail['field_name']) ?>:</label>
                    <div class="info-value editable" 
                         data-detail-id="<?= $detail['id'] ?>"
                         data-school-id="<?= $school_id ?>"
                         onclick="showChangeHistory(<?= $detail['id'] ?>, '<?= htmlspecialchars($detail['field_name']) ?>')">
                        <?= nl2br(htmlspecialchars($detail['field_value'] ?? '')) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <!-- Events Section -->
        <div class="school-section" data-section="events" 
             style="<?= in_array('events', $visible_sections) ? '' : 'display:none;' ?>">
            <div class="section-header">
                <h2 class="section-title">Wydarzenia</h2>
                <button onclick="addEvent(<?= $school_id ?>)" class="btn btn-small btn-primary">+ Dodaj</button>
            </div>
            <?php if (empty($events)): ?>
                <p class="no-data">Brak wydarzeń</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Typ</th>
                            <th>Tytuł</th>
                            <th>Data</th>
                            <th>Godzina</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= htmlspecialchars($event['event_type']) ?></td>
                            <td><?= htmlspecialchars($event['title']) ?></td>
                            <td><?= date('d.m.Y', strtotime($event['event_date'])) ?></td>
                            <td><?= $event['event_time'] ? date('H:i', strtotime($event['event_time'])) : '-' ?></td>
                            <td>
                                <button onclick="editEvent(<?= $event['id'] ?>)" class="btn-icon">✏</button>
                                <button onclick="deleteEvent(<?= $event['id'] ?>)" class="btn-icon">🗑</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Contracts Section -->
        <div class="school-section" data-section="contracts" 
             style="<?= in_array('contracts', $visible_sections) ? '' : 'display:none;' ?>">
            <div class="section-header">
                <h2 class="section-title">Umowy</h2>
                <button onclick="addContract(<?= $school_id ?>)" class="btn btn-small btn-primary">+ Dodaj</button>
            </div>
            <?php if (empty($contracts)): ?>
                <p class="no-data">Brak umów</p>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Numer</th>
                            <th>Data rozpoczęcia</th>
                            <th>Data zakończenia</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contracts as $contract): ?>
                        <tr class="<?= $contract['status'] === 'active' ? '' : 'inactive' ?>">
                            <td><?= htmlspecialchars($contract['contract_number'] ?? '-') ?></td>
                            <td><?= date('d.m.Y', strtotime($contract['start_date'])) ?></td>
                            <td><?= date('d.m.Y', strtotime($contract['end_date'])) ?></td>
                            <td><span class="status-badge status-<?= $contract['status'] ?>"><?= $contract['status'] ?></span></td>
                            <td>
                                <button onclick="editContract(<?= $contract['id'] ?>)" class="btn-icon">✏</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Tasks Section -->
        <div class="school-section" data-section="tasks" 
             style="<?= in_array('tasks', $visible_sections) ? '' : 'display:none;' ?>">
            <div class="section-header">
                <h2 class="section-title">Zadania</h2>
                <button onclick="addTask(<?= $school_id ?>)" class="btn btn-small btn-primary">+ Dodaj</button>
            </div>
            <?php if (empty($tasks)): ?>
                <p class="no-data">Brak zadań</p>
            <?php else: ?>
                <div class="tasks-list">
                    <?php foreach ($tasks as $task): ?>
                    <div class="task-item">
                        <input type="checkbox" 
                               onclick="toggleTaskStatus(<?= $task['id'] ?>)"
                               <?= $task['status'] === 'completed' ? 'checked' : '' ?>>
                        <div class="task-content">
                            <h4><?= htmlspecialchars($task['title']) ?></h4>
                            <p><?= htmlspecialchars($task['description'] ?? '') ?></p>
                            <small>Przypisane do: <?= htmlspecialchars($task['assigned_to_name']) ?></small>
                        </div>
                        <div class="task-date">
                            <?php if ($task['due_date']): ?>
                            <span><?= date('d.m.Y', strtotime($task['due_date'])) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Notes Section -->
        <?php if ($school['notes']): ?>
        <div class="school-section" data-section="notes" 
             style="<?= in_array('notes', $visible_sections) ? '' : 'display:none;' ?>">
            <h2 class="section-title">Notatki</h2>
            <div class="notes-content">
                <?= nl2br(htmlspecialchars($school['notes'])) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Change History Modal -->
    <div id="history-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Historia zmian</h3>
                <button onclick="closeHistoryModal()" class="close-btn">&times;</button>
            </div>
            <div id="history-content" class="modal-body">
                <!-- Populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <script src="public/js/main.js"></script>
    <script src="public/js/school_card.js"></script>
</body>
</html>
