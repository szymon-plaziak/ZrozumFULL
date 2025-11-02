<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = getDBConnection();

// Get change history for a specific field
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['detail_id'])) {
    $detail_id = intval($_GET['detail_id']);
    
    $stmt = $pdo->prepare("
        SELECT ch.*, u.username
        FROM change_history ch
        LEFT JOIN users u ON ch.changed_by = u.id
        WHERE ch.table_name = 'school_details' AND ch.record_id = ?
        ORDER BY ch.changed_at DESC
        LIMIT 50
    ");
    $stmt->execute([$detail_id]);
    $history = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $history
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>
