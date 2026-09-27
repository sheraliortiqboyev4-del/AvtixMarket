<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'madeline.php';

try {
$username = $_GET['username'] ?? null;
$gift_id  = $_GET['gift_id'] ?? null;

if (!$username || !$gift_id) {
echo json_encode([
'ok' => false,
'description' => 'username yoki gift_id yo‘q'
]);
exit;
}
$username = ltrim($username, '@');
$peer = '@' . $username;
$MadelineProto = new \danog\MadelineProto\API('session.madeline');
$MadelineProto->start();

$check = $MadelineProto->payments->checkCanSendGift([
'gift_id' => (int)$gift_id
]);
if ($check['_'] !== 'payments.checkCanSendGiftResultOk') {
echo json_encode([
'ok' => false,
'description' => 'Gift yuborib bo‘lmaydi'
]);
exit;
}
$invoice = [
'_' => 'inputInvoiceStarGift',
'flags' => 1,
'hide_name' => true,
'peer' => $peer,
'gift_id' => (int)$gift_id,
'message' => [
'_' => 'textWithEntities',
'text' => 'Buy and @SoraPayBot',
'entities' => []
]
];
$form = $MadelineProto->payments->getPaymentForm([
'invoice' => $invoice
]);
$result = $MadelineProto->payments->sendStarsForm([
'form_id' => $form['form_id'],
'invoice' => $invoice
]);
echo json_encode([
'ok' => true,
'result' => $result
]);

} catch (Exception $e) {
echo json_encode([
'ok' => false,
'description' => $e->getMessage()
]);
}