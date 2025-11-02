<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
$pdo = getDBConnection();

// Handle export action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_type'])) {
    $export_type = $_POST['export_type'];
    $format = $_POST['format'] ?? 'csv';
    
    $filename = "zrozum_export_" . date('Y-m-d_His') . "." . $format;
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    switch ($export_type) {
        case 'schools':
            // Export schools list
            fputcsv($output, ['ID', 'Nazwa', 'Miasto', 'Kod pocztowy', 'Telefon', 'Email', 'Typ', 'Liczba uczniów']);
            
            $stmt = $pdo->query("SELECT id, name, city, postal_code, phone, email, school_type, student_count FROM schools ORDER BY name");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
            
        case 'school_year_starts':
            // Export school year starts
            fputcsv($output, ['Szkoła', 'Miasto', 'Data rozpoczęcia', 'Godzina', 'Opis']);
            
            $stmt = $pdo->query("
                SELECT s.name, s.city, e.event_date, e.event_time, e.description
                FROM events e
                JOIN schools s ON e.school_id = s.id
                WHERE e.event_type = 'school_year_start'
                ORDER BY e.event_date, s.name
            ");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
            
        case 'meetings':
            // Export meetings
            fputcsv($output, ['Szkoła', 'Miasto', 'Tytuł', 'Data', 'Godzina', 'Opis']);
            
            $stmt = $pdo->query("
                SELECT s.name, s.city, e.title, e.event_date, e.event_time, e.description
                FROM events e
                JOIN schools s ON e.school_id = s.id
                WHERE e.event_type = 'meeting'
                ORDER BY e.event_date, s.name
            ");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
            
        case 'demos':
            // Export demos
            fputcsv($output, ['Szkoła', 'Miasto', 'Tytuł', 'Data', 'Godzina', 'Opis']);
            
            $stmt = $pdo->query("
                SELECT s.name, s.city, e.title, e.event_date, e.event_time, e.description
                FROM events e
                JOIN schools s ON e.school_id = s.id
                WHERE e.event_type = 'demo'
                ORDER BY e.event_date, s.name
            ");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
            
        case 'holidays':
            // Export holidays
            fputcsv($output, ['Szkoła', 'Nazwa święta', 'Data rozpoczęcia', 'Data zakończenia', 'Opis']);
            
            $stmt = $pdo->query("
                SELECT s.name, h.holiday_name, h.start_date, h.end_date, h.description
                FROM holidays h
                LEFT JOIN schools s ON h.school_id = s.id
                ORDER BY h.start_date
            ");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
            
        case 'expiring_contracts':
            // Export expiring contracts
            fputcsv($output, ['Szkoła', 'Miasto', 'Numer umowy', 'Data zakończenia', 'Dni do wygaśnięcia', 'Status']);
            
            $stmt = $pdo->query("
                SELECT s.name, s.city, c.contract_number, c.end_date, 
                       DATEDIFF(c.end_date, CURDATE()) as days_remaining, c.status
                FROM contracts c
                JOIN schools s ON c.school_id = s.id
                WHERE c.status = 'active' AND c.end_date >= CURDATE()
                ORDER BY c.end_date
            ");
            while ($row = $stmt->fetch()) {
                fputcsv($output, $row);
            }
            break;
    }
    
    fclose($output);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksport danych - System Kart Szkół</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Eksport danych</h1>
        </div>
        
        <div class="section">
            <p class="info-text">
                Wybierz typ danych do wyeksportowania. Eksport zostanie zapisany w formacie CSV,
                który można otworzyć w programie Excel lub innym arkuszu kalkulacyjnym.
            </p>
        </div>
        
        <div class="export-grid">
            <!-- Schools Export -->
            <div class="export-card">
                <div class="export-icon">📋</div>
                <h3>Lista szkół</h3>
                <p>Eksportuj listę wszystkich szkół z podstawowymi danymi</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="schools">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
            
            <!-- School Year Starts -->
            <div class="export-card">
                <div class="export-icon">📅</div>
                <h3>Rozpoczęcia roku szkolnego</h3>
                <p>Eksportuj listę dat rozpoczęcia roku szkolnego</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="school_year_starts">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
            
            <!-- Meetings -->
            <div class="export-card">
                <div class="export-icon">🤝</div>
                <h3>Lista zebrań</h3>
                <p>Eksportuj listę wszystkich zebrań</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="meetings">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
            
            <!-- Demos -->
            <div class="export-card">
                <div class="export-icon">🎯</div>
                <h3>Lista pokazowych</h3>
                <p>Eksportuj listę zajęć pokazowych</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="demos">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
            
            <!-- Holidays -->
            <div class="export-card">
                <div class="export-icon">🏖</div>
                <h3>Dni wolne od zajęć</h3>
                <p>Eksportuj listę dni wolnych</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="holidays">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
            
            <!-- Expiring Contracts -->
            <div class="export-card">
                <div class="export-icon">⚠️</div>
                <h3>Wygasające umowy</h3>
                <p>Eksportuj listę nadchodzących terminów zakończenia umów</p>
                <form method="POST" action="export.php">
                    <input type="hidden" name="export_type" value="expiring_contracts">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-primary btn-block">Eksportuj</button>
                </form>
            </div>
        </div>
    </div>
    
    <style>
    .export-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .export-card {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: var(--shadow);
        text-align: center;
    }
    
    .export-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .export-card h3 {
        margin-bottom: 0.5rem;
    }
    
    .export-card p {
        color: var(--secondary-color);
        margin-bottom: 1.5rem;
        min-height: 3rem;
    }
    
    .info-text {
        color: var(--secondary-color);
        line-height: 1.6;
    }
    </style>
    
    <script src="public/js/main.js"></script>
</body>
</html>
