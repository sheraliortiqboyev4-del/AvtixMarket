<?php
/**
 * User Orders API
 * Returns user's order history
 */

require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    sendResponse(false, 'user_id parameter required');
}

try {
    $user_id = $_GET['user_id'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;

    $stmt = $pdo->prepare("
        SELECT 
            id,
            order_id,
            turi,
            quantity,
            mountity,
            amount,
            hamyon_ton,
            quantity_ton,
            status,
            created_at,
            updated_at
        FROM orders 
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    $orders = $stmt->fetchAll();

    sendResponse(true, 'Orders retrieved', ['orders' => $orders]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("[ERROR] User orders: " . $e->getMessage());
    sendResponse(false, 'Error retrieving orders');
}

?>
