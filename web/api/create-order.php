<?php
/**
 * Create Order API
 * Handles order creation from web app
 */

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendResponse(false, 'Method not allowed');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['user_id']) || !isset($input['items']) || empty($input['items'])) {
        http_response_code(400);
        sendResponse(false, 'Missing required fields');
    }

    $user_id = $input['user_id'];
    $username = $input['username'] ?? 'user';
    $items = $input['items'];
    $payment_method = $input['payment_method'] ?? 'manual';

    // Calculate total
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += $item['price'];
    }

    // Get or create user
    $stmt = $pdo->prepare("
        SELECT id, user_id FROM users WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        // Create new user
        $stmt = $pdo->prepare("
            INSERT INTO users (user_id, username, balance, ref, sana)
            VALUES (?, ?, 0, 0, NOW())
        ");
        $stmt->execute([$user_id, $username]);
    }

    // Process each item and create orders
    $order_ids = [];
    
    foreach ($items as $item) {
        $order_id = generateOrderId();
        $type = $item['type'];
        
        // Prepare order data
        $hamyon_ton = $item['tonWallet'] ?? null;
        $quantity = $item['quantity'] ?? null;
        $months = $item['months'] ?? null;
        $amount = $item['amount'] ?? null;
        $gift_id = $item['gift_id'] ?? null;

        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, username, order_id, turi, quantity, 
                mountity, amount, hamyon_ton, quantity_ton, 
                gift_id, gift, custom_emoji, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");

        $stmt->execute([
            $user_id,
            $username,
            $order_id,
            $type,
            $item['quantity'] ?? 0,
            $item['months'] ?? 0,
            $item['price'],
            $hamyon_ton,
            $item['amount'] ?? 0,
            null,
            null,
            null
        ]);

        $order_ids[] = $order_id;

        // Log creation
        logAction($user_id, 'order_created', [
            'order_id' => $order_id,
            'type' => $type,
            'amount' => $item['price'],
            'payment_method' => $payment_method
        ]);
    }

    // Notify admin
    $notification_text = "📝 <b>Yangi Buyurtma Yuklandi</b>\n\n";
    $notification_text .= "👤 <b>Foydalanuvchi:</b> @$username (ID: $user_id)\n";
    $notification_text .= "💰 <b>Jami Summa:</b> " . number_format($total_amount) . " so'm\n";
    $notification_text .= "📦 <b>Buyurtma Soni:</b> " . count($items) . "\n";
    $notification_text .= "🔗 <b>Order IDs:</b> " . implode(", ", $order_ids) . "\n\n";
    
    $notification_text .= "<b>Mahsulotlar:</b>\n";
    foreach ($items as $item) {
        $notification_text .= "• " . $item['type'] . " - " . $item['price'] . " so'm\n";
    }

    $admin_id = 2142292702; // Admin Telegram ID
    
    $ch = curl_init(TELEGRAM_API_URL . '/sendMessage');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'chat_id' => $admin_id,
        'text' => $notification_text,
        'parse_mode' => 'HTML'
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    sendResponse(true, 'Order created successfully', [
        'order_id' => $order_ids[0],
        'orders' => $order_ids,
        'total' => $total_amount
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("[ERROR] Create order: " . $e->getMessage());
    sendResponse(false, 'Error creating order: ' . $e->getMessage());
}

?>
