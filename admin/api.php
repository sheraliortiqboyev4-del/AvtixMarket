<?php
/**
 * SoraPay Admin Panel API
 * Mavjud bazalar bilan ishlaydi
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
http_response_code(200);
exit;
}

date_default_timezone_set('Asia/Tashkent');

class Database {
private $host = 'localhost';
private $db_name = 'stars_bot';
private $username = 'stars_user';
private $password = 'StarsBot_2026!';
private $charset = 'utf8mb4';
private $conn;

public function getConnection() {
$this->conn = null;

try {
$dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
$this->conn = new PDO($dsn, $this->username, $this->password);
$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
} catch (PDOException $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => 'Database connection failed: ' . $e->getMessage()
]);
exit;
}
return $this->conn;
}
}

$database = new Database();
$conn = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : null;

if ($action === 'get_orders') {
getOrders($conn);
} elseif ($action === 'get_users') {
getUsers($conn);
} elseif ($action === 'get_transactions') {
getTransactions($conn);
} elseif ($action === 'complete_order') {
completeOrder($conn);
} elseif ($action === 'cancel_order') {
cancelOrder($conn);
} elseif ($action === 'get_logs') {
getLogs($conn);
} else {
http_response_code(404);
echo json_encode(['error' => 'Action not found']);
}

function getOrders($conn) {
try {
$status = isset($_GET['status']) ? $_GET['status'] : null;
$turi = isset($_GET['turi']) ? $_GET['turi'] : null;
        
$query = "
SELECT 
id, order_id, user_id, username, turi, 
quantity, mountity, amount, status, 
hamyon_ton, quantity_ton, gift_id, gift, custom_emoji,
created_at, updated_at
FROM orders 
WHERE 1=1
";
        
$params = [];
        
if ($status) {
$query .= " AND status = ?";
$params[] = $status;
}
        
if ($turi) {
$query .= " AND turi = ?";
$params[] = $turi;
}
        
$query .= " ORDER BY created_at DESC LIMIT 200";
        
$stmt = $conn->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();
        
echo json_encode([
'success' => true,
'data' => $orders,
'count' => count($orders)
]);
} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

function getUsers($conn) {
try {
$stmt = $conn->prepare("
SELECT 
user_id, username, 
COUNT(*) as total_orders,
SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_orders,
SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_spent
FROM orders 
GROUP BY user_id, username
ORDER BY total_spent DESC
LIMIT 100
");
$stmt->execute();
$users = $stmt->fetchAll();
        
echo json_encode([
'success' => true,
'data' => $users,
'count' => count($users)
]);
} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

function getTransactions($conn) {
try {
$stmt = $conn->prepare("
SELECT 
t.id,
o.order_id, 
o.user_id, 
o.username, 
o.turi, 
o.amount, 
o.status,
t.payme_id, 
t.state, 
t.create_time, 
t.perform_time,
t.transaction_id,
o.created_at
FROM transactions t
LEFT JOIN orders o ON t.order_id = o.id
ORDER BY o.created_at DESC 
LIMIT 100
");
$stmt->execute();
$transactions = $stmt->fetchAll();
        
echo json_encode([
'success' => true,
'data' => $transactions,
'count' => count($transactions)
]);
} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

function completeOrder($conn) {
try {
$input = json_decode(file_get_contents('php://input'), true);
error_log("[DEBUG] completeOrder called with input: " . json_encode($input));
if (!isset($input['order_id'])) {
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'order_id required']);
return;
}

$order_id = $input['order_id'];
error_log("[DEBUG] Looking for order with id: " . $order_id);

$stmt = $conn->prepare("
SELECT * FROM orders 
WHERE id = ? OR order_id = ?
");
$stmt->execute([$order_id, $order_id]);
$order = $stmt->fetch();
        
error_log("[DEBUG] Order found: " . ($order ? json_encode($order) : 'NOT FOUND'));

if (!$order) {
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Order not found']);
return;
}

if ($order['status'] === 'paid') {
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Order already paid']);
return;
}

$conn->beginTransaction();
        
try {
$update_stmt = $conn->prepare("
UPDATE orders 
SET status = 'paid', updated_at = NOW() 
WHERE id = ?
");
$update_stmt->execute([$order['id']]);

$log_stmt = $conn->prepare("
INSERT INTO admin_logs (admin_id, action, details, created_at) 
VALUES (?, ?, ?, NOW())
");
$log_stmt->execute([
1,
'complete_order',
json_encode([
'order_id' => $order['order_id'],
'amount' => $order['amount'],
'user' => $order['username']
])
]);

$conn->commit();

$turi = $order['turi'];
$hamyon_ton = $order['hamyon_ton'];
$quantity_ton = $order['quantity_ton'];
$order_id_str = $order['order_id'];      
$gift_id = $order['gift_id'];
$emoj = $order['gift'];
$emoji = $order['custom_emoji'];
$user_id = $order['user_id'];
$username = $order['username'];
$quantity = $order['quantity'] ?? 0;
$month = $order['mountity'] ?? 0;
$amount = (int)$order['amount'];

$telegram_api_url = "https://api.telegram.org/bot8928348194:AAE1bvqdRMj43ZRVPjbsMzSk8VU7bWk9uxk/sendMessage";

$success = false;
$error_message = "";
$text = "";

if ($turi === 'stars' || $turi === 'star') {
$api_url = "https://sora.sheralidev.uz/BuyStars/main.php?" . http_build_query([
'action' => 'stars',
'username' => $username,
'amount' => $quantity
]);
$response = @file_get_contents($api_url);
$result = json_decode($response, true);

if ($result && isset($result['status']) && $result['status'] === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n<blockquote>▪️<b>Turi:</b> Telegram Stars\n▪️<b>Username:</b> @$username\n▪️<b>Soni:</b> $quantity-ta\n▪️<b>Summa:</b> " . number_format($amount) . " so'm\n▪️<b>ID:</b> [$order_id_str]</blockquote>";
} else {
$error_message = "Stars sotib olish API da xatolik yuz berdi.";
}
}
elseif ($turi === 'premium') {
$api_url = "https://sora.sheralidev.uz/BuyPremium/main.php?" . http_build_query([
'action' => 'premium',
'amount' => $month,
'username' => $username
]);
$response = @file_get_contents($api_url);
$result = json_decode($response, true);

if ($result && isset($result['status']) && $result['status'] === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n<blockquote>▪️<b>Turi:</b> Telegram Premium\n▪️<b>Username:</b> @$username\n▪️<b>Muddati:</b> $month-Oy\n▪️<b>Summa:</b> " . number_format($amount) . " so'm\n▪️<b>ID:</b> [$order_id_str]</blockquote>";
} else {
$error_message = "Premium sotib olish API da xatolik yuz berdi.";
}
}
elseif ($turi === 'ton') {
$api_url = "https://sora.sheralidev.uz/BuyTon/main.php?" . http_build_query([
'recipient' => $hamyon_ton,
'amount' => $quantity_ton
]);
$response = @file_get_contents($api_url);
$result = json_decode($response, true);

if ($result && isset($result['status']) && $result['status'] === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n<blockquote>▪️<b>Turi:</b> TON\n▪️<b>TON Wallet:</b> <code>$hamyon_ton</code>\n▪️<b>Ton Miqdori:</b> $quantity_ton ta\n▪️<b>Summa:</b> " . number_format($amount) . " so'm\n▪️<b>ID:</b> [$order_id_str]</blockquote>";
} else {
$error_message = "TON yuborish API da xatolik yuz berdi.";
}
}
elseif ($turi === 'gift') {
$api_url = "https://sora.sheralidev.uz/Gift_card/gift_card.php?username={$username}&gift_id={$emoji}";
$response = @file_get_contents($api_url);
$result = json_decode($response, true);

if ($result && isset($result['ok']) && $result['ok'] === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n<blockquote>▪️<b>Turi:</b> Telegram Gift\n▪️<b>Username:</b> @$username\n▪️<b>Gift:</b> <tg-emoji emoji-id=\"" . htmlspecialchars($gift_id) . "\">" . htmlspecialchars($emoj) . "</tg-emoji>\n▪️<b>Summa:</b> " . number_format($amount) . " so'm\n▪️<b>ID:</b> [$order_id]</blockquote>";
} else {
$error_message = "Gift yuborishda Telegram API xatosi: " . ($result['description'] ?? 'Noma\'lum xatolik');
}
}

if ($success && !empty($text)) {
$text .= "\n<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";

$params = [
'chat_id' => $user_id,
'text' => $text,
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]],
[['text' => "Orqaga", 'callback_data' => "back_to_main"]]
]
])
];

error_log("[DEBUG] Sending Telegram notification to user_id: $user_id");
error_log("[DEBUG] Message params: " . json_encode($params));

$ch = curl_init($telegram_api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$telegram_response = curl_exec($ch);
curl_close($ch);
                
error_log("[DEBUG] Telegram response: " . $telegram_response);
                
$channel_text = str_replace(
"<tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi",
"✅ Buyurtma bajarildi!",  
$text
);

$channel_params = [
'chat_id'    => -1003991077401,
'text'       => $channel_text,
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]]
]
])
];

$ch = curl_init($telegram_api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($channel_params));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_exec($ch);
curl_close($ch);

} 
elseif (!$success) {
$params = [
'chat_id' => $user_id,
'text' => "<b><tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> Xatolik yuz berdi!</b>\n\n<i>To'lov qabul qilindi, lekin buyurtmani yetkazishda muammo chiqdi.</i>\n<i>Iltimos, administratorga quyidagi ma'lumotlar bilan murojaat qiling:</i>\n<blockquote><b>Order ID:</b> <code>$order_id_str</code>\n<b>Turi:</b> $turi</blockquote>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]]
]
])
];

$ch = curl_init($telegram_api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

$admin_text = "🚨 API XATOLIK (completeOrder)\n\n";
$admin_text .= "Order ID: $order_id_str\n";
$admin_text .= "Turi: $turi\n";
$admin_text .= "User: @$username (ID: $user_id)\n";
$admin_text .= "Summa: " . number_format($amount) . " so'm\n";
if (!empty($error_message)) $admin_text .= "Xabar: " . $error_message;

$admin_params = [
'chat_id' => 2142292702,
'text' => $admin_text,
'parse_mode' => 'HTML'
];

$ch = curl_init($telegram_api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($admin_params));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

error_log("API FAILURE - Order: $order_id_str | Type: $turi | Error: " . $error_message);
}

echo json_encode([
'success' => true,
'message' => 'Order completed successfully',
'order' => $order
]);

} catch (Exception $notifyException) {
$conn->rollBack();
error_log("NOTIFY ERROR (completeOrder): " . $notifyException->getMessage() . " | Order ID: " . $order['id']);
throw $notifyException;
}

} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

function cancelOrder($conn) {
try {
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['order_id'])) {
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'order_id required']);
return;
}

$order_id = $input['order_id'];

$stmt = $conn->prepare("
SELECT * FROM orders 
WHERE id = ? OR order_id = ?
");
$stmt->execute([$order_id, $order_id]);
$order = $stmt->fetch();
if (!$order) {
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'Order not found']);
return;
}

$update_stmt = $conn->prepare("
UPDATE orders 
SET status = 'cancelled', updated_at = NOW() 
WHERE id = ?
");
$update_stmt->execute([$order['id']]);

echo json_encode([
'success' => true,
'message' => 'Order cancelled successfully'
]);

} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

function getLogs($conn) {
try {
$stmt = $conn->prepare("
SELECT 
id, admin_id, action, details, created_at
FROM admin_logs 
ORDER BY created_at DESC 
LIMIT 100
");
$stmt->execute();
$logs = $stmt->fetchAll();
        
echo json_encode([
'success' => true,
'data' => $logs,
'count' => count($logs)
]);
} catch (Exception $e) {
http_response_code(500);
echo json_encode([
'success' => false,
'error' => $e->getMessage()
]);
}
}

?>