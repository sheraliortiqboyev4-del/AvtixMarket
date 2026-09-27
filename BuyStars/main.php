<?php
header("Content-Type: application/json");

$username = $_GET['username'] ?? null;
$action   = $_GET['action']   ?? null; // 'stars' yoki 'premium'
$amount   = $_GET['amount']   ?? null; // stars: 50,100... | premium: 1,3,6,12

if (!$username || !$action || !$amount) {
    echo json_encode([
        "status" => false,
        "error"  => "username, action (stars|premium) va amount parametrlari talab qilinadi"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($action, ['stars', 'premium'])) {
    echo json_encode([
        "status" => false,
        "error"  => "action faqat 'stars' yoki 'premium' bo'lishi mumkin"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$clean_username = ltrim($username, '@');
$action_esc     = escapeshellarg($action);
$username_esc   = escapeshellarg($clean_username);
$amount_esc     = escapeshellarg($amount);

$python  = __DIR__ . "/venv/bin/python";
$script  = __DIR__ . "/main.py";

$command = "$python $script $action_esc $username_esc $amount_esc 2>&1";
$output  = shell_exec($command);

// --- Parse ---
$status         = false;
$message        = "Noma'lum xato yuz berdi";
$amount_ton     = null;
$tx_hash        = null;
$recipient_name = null;
$sent           = false;

if (preg_match('/Сумма для отправки:\s*([\d.]+)\s*TON/i', $output, $matches)) {
    $amount_ton = floatval($matches[1]);
}

if (preg_match('/✅ Транзакция отправлена:\s*([0-9a-fA-F]+)/i', $output, $matches)) {
    $tx_hash = $matches[1];
    $status  = true;
    $sent    = true;
    $label   = $action === 'stars' ? 'Telegram Stars' : 'Telegram Premium';
    $message = "✅ Tolov muvaffaqiyatli amalga oshirildi. $label yuborildi.";
} elseif (preg_match('/Ошибка:/i', $output)) {
    $message = "❌ Tolov amalga oshirilmadi (xato).";
} elseif (strpos($output, 'Traceback') !== false || strpos($output, 'Exception') !== false) {
    $message = "❌ Script ishida xato yuz berdi.";
} else {
    $message = "❌ Tolov amalga oshirilmadi (tranzaksiya topilmadi).";
}

if (preg_match_all('/\{.*?"found".*?"name"\s*:\s*"([^"]+)"\s*.*?\}/s', $output, $m)) {
    $recipient_name = $m[1][0] ?? null;
} elseif (preg_match_all('/\{.*?"name"\s*:\s*"([^"]+)"\s*.*?\}/s', $output, $m)) {
    $recipient_name = $m[1][0] ?? null;
}

echo json_encode([
    "status"          => $status,
    "message"         => $message,
    "action"          => $action,
    "username"        => $username,
    "amount"          => (int)$amount,
    "amount_ton"      => $amount_ton,
    "tx_hash"         => $tx_hash,
    "sent"            => $sent,
    "payment_success" => $status
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
