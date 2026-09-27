<?php
header("Content-Type: application/json");

$recipient = $_GET['recipient'] ?? null; 
$amount    = $_GET['amount'] ?? null; 

if (!$recipient || !$amount) {
echo json_encode([
"status" => false,
"error" => "recipient yoki amount berilmagan"
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
}

$python = __DIR__ . "/venv/bin/python";
$script = __DIR__ . "/main.py";

$recipient_esc = escapeshellarg($recipient);
$amount_esc    = escapeshellarg($amount);

$command = "$python $script $recipient_esc $amount_esc 2>&1";
$output = shell_exec($command);

$status = strpos($output, "✅ Транзакция отправлена:") !== false || strpos($output, "STATUS: SUCCESS") !== false;

$result = [
"status"          => $status,
"message"         => $status ? "✅ Tolov muvaffaqiyatli yuborildi" : "❌ Tolov amalga oshirilmadi",
"recipient"       => $recipient,
"amount_ton"      => floatval($amount),
"tx_hash"         => null,
"raw_output"      => $output  
];

if (preg_match('/Транзакция отправлена:\s*([0-9a-fA-F]+)/i', $output, $m)) {
$result['tx_hash'] = $m[1];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>