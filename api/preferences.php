<?php
require_once '../config/database.php';
require_once '../config/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = getCurrentUser();
$pdo = getDBConnection();

// Save preference
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $key = $input['key'] ?? '';
    $value = $input['value'] ?? '';
    
    if (!$key) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing preference key']);
        exit;
    }
    
    // Convert value to JSON if it's an array
    if (is_array($value)) {
        $value = json_encode($value);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO user_preferences (user_id, preference_key, preference_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE preference_value = VALUES(preference_value)
    ");
    $stmt->execute([$user['id'], $key, $value]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Preference saved'
    ]);
    exit;
}

// Get preference
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['key'])) {
    $key = $_GET['key'];
    
    $stmt = $pdo->prepare("
        SELECT preference_value FROM user_preferences 
        WHERE user_id = ? AND preference_key = ?
    ");
    $stmt->execute([$user['id'], $key]);
    $value = $stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'data' => $value
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>
