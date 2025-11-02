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

// Handle GET request - get detail value
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $detail_id = intval($_GET['id']);
    
    $stmt = $pdo->prepare("SELECT * FROM school_details WHERE id = ?");
    $stmt->execute([$detail_id]);
    $detail = $stmt->fetch();
    
    if ($detail) {
        echo json_encode(['success' => true, 'data' => $detail]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Detail not found']);
    }
    exit;
}

// Handle PUT request - update detail value
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT')) {
    $input = json_decode(file_get_contents('php://input'), true);
    $detail_id = intval($input['id'] ?? 0);
    $new_value = $input['value'] ?? '';
    
    if (!$detail_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid detail ID']);
        exit;
    }
    
    // Get current value
    $stmt = $pdo->prepare("SELECT * FROM school_details WHERE id = ?");
    $stmt->execute([$detail_id]);
    $detail = $stmt->fetch();
    
    if (!$detail) {
        http_response_code(404);
        echo json_encode(['error' => 'Detail not found']);
        exit;
    }
    
    $old_value = $detail['field_value'];
    
    // Update the value
    $stmt = $pdo->prepare("
        UPDATE school_details 
        SET field_value = ?, updated_by = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$new_value, $user['id'], $detail_id]);
    
    // Log change history
    $stmt = $pdo->prepare("
        INSERT INTO change_history (table_name, record_id, field_name, old_value, new_value, changed_by)
        VALUES ('school_details', ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $detail_id,
        $detail['field_name'],
        $old_value,
        $new_value,
        $user['id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Detail updated successfully',
        'data' => [
            'id' => $detail_id,
            'old_value' => $old_value,
            'new_value' => $new_value
        ]
    ]);
    exit;
}

// Handle POST request - add new detail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_method'])) {
    $school_id = intval($_POST['school_id'] ?? 0);
    $section = $_POST['section'] ?? '';
    $field_name = $_POST['field_name'] ?? '';
    $field_value = $_POST['field_value'] ?? '';
    
    if (!$school_id || !$section || !$field_name) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO school_details (school_id, section, field_name, field_value, updated_by)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$school_id, $section, $field_name, $field_value, $user['id']]);
    
    $detail_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Detail added successfully',
        'data' => ['id' => $detail_id]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
?>
