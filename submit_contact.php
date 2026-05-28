<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST');

$host = 'localhost';
$db   = 'awais_portfolio';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Auto-create table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($createTableSQL);
    
} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input or Form POST input
    $input = json_decode(file_get_contents('php://input'), true);
    
    $first_name = trim($input['first_name'] ?? $_POST['first_name'] ?? '');
    $last_name  = trim($input['last_name'] ?? $_POST['last_name'] ?? '');
    $email      = trim($input['email'] ?? $_POST['email'] ?? '');
    $subject    = trim($input['subject'] ?? $_POST['subject'] ?? '');
    $message    = trim($input['message'] ?? $_POST['message'] ?? '');
    
    // Validate inputs
    if (empty($first_name) || empty($email) || empty($message)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill in all required fields (First Name, Email, and Message).'
        ]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email address.'
        ]);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare('INSERT INTO messages (first_name, last_name, email, subject, message) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$first_name, $last_name, $email, $subject, $message]);
        
        echo json_encode([
            'status' => 'success',
            'message' => '✓ Message sent! I\'ll get back to you soon.'
        ]);
    } catch (\PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to save message: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
