<?php
/**
 * User Data API
 * Returns user info, balance, referral data
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

    // Get user data
    $stmt = $pdo->prepare("
        SELECT 
            user_id, 
            username, 
            balance, 
            ref,
            user_ref_id,
            sana
        FROM users 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        sendResponse(false, 'User not found');
    }

    // Count referrals
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM users WHERE ref_id = ?
    ");
    $stmt->execute([$user['user_ref_id']]);
    $ref_count = $stmt->fetch()['count'];

    // Calculate referral bonus (if any)
    $ref_bonus = $ref_count * 5000; // Example: 5000 so'm per referral

    $userData = [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'balance' => (int)$user['balance'],
        'ref_id' => $user['user_ref_id'],
        'ref_count' => $ref_count,
        'ref_bonus' => $ref_bonus,
        'bot_username' => BOT_USERNAME,
        'joined' => $user['sana']
    ];

    sendResponse(true, 'User data retrieved', ['data' => $userData]);

} catch (Exception $e) {
    http_response_code(500);
    error_log("[ERROR] User data: " . $e->getMessage());
    sendResponse(false, 'Error retrieving user data');
}

?>
