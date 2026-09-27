<?php
header('Content-Type: application/json; charset=utf-8');

require_once 'madeline.php';

try {
$MadelineProto = new \danog\MadelineProto\API('session.madeline');
$MadelineProto->start();

$starsStatus = $MadelineProto->payments->getStarsStatus([
'peer' => ['_' => 'inputPeerSelf']
]);

$balance = (int) ($starsStatus['balance']['amount'] ?? 0);
$timestamp = date('Y-m-d H:i:s');
$jsonData = ['time' => $timestamp,'balance' => $balance,'unix_time' => time()];
file_put_contents('stars_balance.json', json_encode($jsonData, JSON_PRETTY_PRINT));

echo json_encode([
'ok' => true,
'balance' => $balance,
'updated_at' => $timestamp,
'description' => 'Balans muvaffaqiyatli yangilandi'
]);

} catch (Exception $e) {
$errorMsg = $e->getMessage();
file_put_contents('stars_balance_error.log', date('Y-m-d H:i:s') . " | " . $errorMsg . "\n", FILE_APPEND);
    
echo json_encode([
'ok' => false,
'description' => $errorMsg
]);
}