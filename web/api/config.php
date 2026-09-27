<?php
/**
 * Database Configuration
 */

date_default_timezone_set('Asia/Tashkent');

$host = 'localhost';
$db_name = 'stars_bot';
$username = 'stars_user';
$password = 'StarsBot_2026!';
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]));
}

// Telegram Bot Token
define('BOT_TOKEN', '8476376332:AAExUcQEt4jAw8srGfa4AHOrtgR4NiRTtJ8');
define('BOT_USERNAME', 'AvtixMarketBot');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN);

// External APIs for fulfillment
define('STARS_API', 'https://sora.sheralidev.uz/BuyStars/main.php');
define('PREMIUM_API', 'https://sora.sheralidev.uz/BuyPremium/main.php');
define('TON_API', 'https://sora.sheralidev.uz/BuyTon/main.php');
define('GIFT_API', 'https://sora.sheralidev.uz/Gift_card/gift_card.php');

// Helper function: Generate unique ID
function generateOrderId() {
    return 'ORD-' . time() . '-' . rand(1000, 9999);
}

// Helper function: Send JSON response
function sendResponse($success, $message = '', $data = null) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        foreach ($data as $key => $value) {
            $response[$key] = $value;
        }
    }
    
    echo json_encode($response);
    exit;
}

// Helper function: Log action
function logAction($user_id, $action, $details) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, details, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $action, json_encode($details)]);
    } catch (Exception $e) {
        // Silently fail logging
    }
}

?>
