<?php
$connect = mysqli_connect('localhost', 'stars_user', "StarsBot_2026!", 'stars_bot');

define('TONAPI_KEY', 'AH6VGUQHYLVXFGQAAAAE6SCSDCDSXQAYSSPI4ZLUERNDXWDVI');
define('BOT_TOKEN',   '8476376332:AAExUcQEt4jAw8srGfa4AHOrtgR4NiRTtJ8');


$raw = file_get_contents('php://input');
if (empty($raw)) {
http_response_code(400);
exit();
}

$payload = json_decode($raw, true);
if (!isset($payload['tx_hash'], $payload['account_id'])) {
http_response_code(400);
exit();
}

http_response_code(200);
echo 'ok';

if (function_exists('fastcgi_finish_request')) {
fastcgi_finish_request();
} else {
ob_end_flush();
flush();
}


$tx_hash = $payload['tx_hash'];
$lt      = $payload['lt'] ?? null;

$tx = getTonTransaction($tx_hash);
if (!$tx) {
error_log("[TON WEBHOOK] tx_hash topilmadi: $tx_hash");
exit();
}

$in_msg = $tx['in_msg'] ?? null;
if (!$in_msg || empty($in_msg['value'])) {
exit(); 
}

$comment = '';
if (!empty($in_msg['decoded_body']['text'])) {
$comment = trim($in_msg['decoded_body']['text']);
} elseif (!empty($in_msg['decoded_body']['comment'])) {
$comment = trim($in_msg['decoded_body']['comment']);
}

if (empty($comment)) {
exit();
}

$decoded = base64_decode($comment, true);
if ($decoded !== false && preg_match('/^SORA-\d+$/', $decoded)) {
$order_id = $decoded;
} else {
$order_id = $comment;
}
 
$amount_ton = ($in_msg['value'] ?? 0) / 1000000000;


$order_id_safe = mysqli_real_escape_string($connect, $order_id);
$order = mysqli_fetch_assoc(mysqli_query($connect,
"SELECT * FROM orders WHERE order_id='$order_id_safe' AND status='pending' LIMIT 1"
));

if (!$order) {
error_log("[TON WEBHOOK] Buyurtma topilmadi yoki allaqachon to'langan: $order_id");
exit();
}


$expected_ton = (float)($order['ton_amount'] ?? 0);
 
if ($expected_ton > 0) {
$tolerance = $expected_ton * 0.05; // 5% farq ruxsat (kurs farqi uchun)
if (abs($amount_ton - $expected_ton) > $tolerance) {
error_log("[TON WEBHOOK] Noto'g'ri TON miqdori: keldi={$amount_ton}, kutilgan={$expected_ton}, order={$order_id}");
$tg_url_early = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
tg_send($tg_url_early, 2142292702,
"⚠️ <b>Noto'g'ri TON miqdori!</b>\n\n"
. "Order: <code>$order_id</code>\n"
. "Kelgan: <b>{$amount_ton} TON</b>\n"
. "Kutilgan: <b>{$expected_ton} TON</b>\n"
. "TX: <code>$tx_hash</code>\n\n"
. "Buyurtma bajarilmadi. Foydalanuvchi bilan bog'laning."
);
exit();
}
}



mysqli_query($connect,
"UPDATE orders SET status='paid'
WHERE order_id='$order_id_safe' AND status='pending'"
);
if (mysqli_affected_rows($connect) === 0) {
error_log("[TON WEBHOOK] Allaqachon bajarilgan yoki topilmadi: $order_id");
exit();
}

$turi        = $order['turi'];
$user_id     = $order['user_id'];
$username    = $order['username'] ?? '';
$quantity    = $order['quantity'] ?? 0;
$month       = $order['mountity'] ?? 0;
$hamyon_ton  = $order['hamyon_ton'] ?? '';
$quantity_ton = $order['quantity_ton'] ?? 0;
$gift_id     = $order['gift_id'] ?? '';
$emoj        = $order['gift'] ?? '';
$emoji       = $order['custom_emoji'] ?? '';
$amount_uzs  = (int)$order['amount'];

$base = "https://sora.sheralidev.uz";

$success = false;
$text    = '';
$error_message = '';

try {
if ($turi === 'stars' || $turi === 'star') {
$api_url  = "$base/BuyStars/main.php?" . http_build_query(['quantity' => $quantity, 'username' => $username]);
$response = @file_get_contents($api_url);
$result   = json_decode($response, true);
if ($result && ($result['status'] ?? false) === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n"
. "<blockquote>▪️<b>Turi:</b> Telegram Stars\n"
. "▪️<b>Username:</b> @$username\n"
. "▪️<b>Soni:</b> {$quantity}-ta\n"
. "▪️<b>Summa:</b> " . number_format($amount_uzs) . " so'm\n"
. "▪️<b>To'lov:</b> TON\n"
. "▪️<b>ID:</b> [$order_id]</blockquote>"
. "<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";
} else {
$error_message = "Stars API xatolik: " . json_encode($result);
}
} elseif ($turi === 'premium') {
$api_url  = "$base/BuyPremium/main.php?" . http_build_query(['mountity' => $month, 'username' => $username]);
$response = @file_get_contents($api_url);
$result   = json_decode($response, true);
if ($result && ($result['status'] ?? false) === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n"
. "<blockquote>▪️<b>Turi:</b> Telegram Premium\n"
. "▪️<b>Username:</b> @$username\n"
. "▪️<b>Muddati:</b> {$month}-Oy\n"
. "▪️<b>Summa:</b> " . number_format($amount_uzs) . " so'm\n"
. "▪️<b>To'lov:</b> TON\n"
. "▪️<b>ID:</b> [$order_id]</blockquote>"
. "<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";
} else {
$error_message = "Premium API xatolik: " . json_encode($result);
}
} elseif ($turi === 'ton') {
$api_url  = "$base/BuyTon/main.php?" . http_build_query(['recipient' => $hamyon_ton, 'amount' => $quantity_ton]);
$response = @file_get_contents($api_url);
$result   = json_decode($response, true);
if ($result && ($result['status'] ?? false) === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n"
. "<blockquote>▪️<b>Turi:</b> TON\n"
. "▪️<b>TON Wallet:</b> <code>$hamyon_ton</code>\n"
. "▪️<b>Ton Miqdori:</b> $quantity_ton ta\n"
. "▪️<b>Summa:</b> " . number_format($amount_uzs) . " so'm\n"
. "▪️<b>To'lov:</b> TON\n"
. "▪️<b>ID:</b> [$order_id]</blockquote>"
. "<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";
} else {
$error_message = "TON API xatolik: " . json_encode($result);
}

} elseif ($turi === 'gift') {
$api_url  = "$base/Gift_card/gift_card.php?username={$username}&gift_id={$emoji}";
$response = @file_get_contents($api_url);
$result   = json_decode($response, true);
if ($result && ($result['ok'] ?? false) === true) {
$success = true;
$text = "<b><tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi</b>\n"
. "<blockquote>▪️<b>Turi:</b> Telegram Gift\n"
. "▪️<b>Username:</b> @$username\n"
. "▪️<b>Gift:</b> <tg-emoji emoji-id=\"" . htmlspecialchars($gift_id) . "\">" . htmlspecialchars($emoj) . "</tg-emoji>\n"
. "▪️<b>Summa:</b> " . number_format($amount_uzs) . " so'm\n"
. "▪️<b>To'lov:</b> TON\n"
. "▪️<b>ID:</b> [$order_id]</blockquote>"
. "<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";
} else {
$error_message = "Gift API xatolik: " . ($result['description'] ?? json_encode($result));
}
}
} catch (Exception $e) {
$error_message = $e->getMessage();
}

$tg_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";

if ($success) {
$full_text = $text . "\n<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Agarda buyurtma 1 daqiqa ichida yetkazilmasa iltimos administrator bilan bog'laning!</b>";

tg_send($tg_url, $user_id, $full_text, [
'inline_keyboard' => [
[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]],
[['text' => "Orqaga", 'callback_data' => "back_to_main"]]
]
]);

$channel_text = str_replace(
"<tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> To'lovingiz qabul qilindi",
"✅ Buyurtma bajarildi!",
$text
);
tg_send($tg_url, -1003991077401, $channel_text, [
'inline_keyboard' => [
[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]]
]
]);
} else {
mysqli_query($connect, "UPDATE orders SET status='pending' WHERE order_id='$order_id_safe'");

tg_send($tg_url, $user_id,
"<b><tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> Xatolik yuz berdi!</b>\n\n"
. "<i>To'lov qabul qilindi, lekin buyurtmani yetkazishda muammo chiqdi.</i>\n"
. "<i>Iltimos, administratorga quyidagi ma'lumotlar bilan murojaat qiling:</i>\n"
. "<blockquote><b>Order ID:</b> <code>$order_id</code>\n"
. "<b>Turi:</b> $turi</blockquote>",
['inline_keyboard' => [[['text' => "Administrator", 'url' => "tg://user?id=2142292702"]]]]
);

$admin_text = "🚨 <b>TON WEBHOOK — API XATOLIK</b>\n\n"
. "Order ID: <code>$order_id</code>\n"
. "TX Hash: <code>$tx_hash</code>\n"
. "Turi: $turi\n"
. "User: @$username (ID: $user_id)\n"
. "Summa: " . number_format($amount_uzs) . " so'm\n"
. "TON miqdori: $amount_ton TON\n"
. "Xabar: $error_message";
tg_send($tg_url, 2142292702, $admin_text);

error_log("[TON WEBHOOK] API FAILURE — Order: $order_id | Turi: $turi | $error_message");
}

function getTonTransaction(string $tx_hash): ?array {
$url = "https://tonapi.io/v2/blockchain/transactions/$tx_hash";
$ch  = curl_init($url);
curl_setopt_array($ch, [
CURLOPT_RETURNTRANSFER => true,
CURLOPT_TIMEOUT        => 10,
CURLOPT_HTTPHEADER     => [
"Authorization: Bearer " . TONAPI_KEY,
"Content-Type: application/json"
]
]);
$response  = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || empty($response)) {
return null;
}
return json_decode($response, true);
}

function tg_send(string $url, int $chat_id, string $text, array $keyboard = null): void {
$params = [
'chat_id'                  => $chat_id,
'text'                     => $text,
'parse_mode'               => 'HTML',
'disable_web_page_preview' => true,
];
if ($keyboard) {
$params['reply_markup'] = json_encode($keyboard);
}
$ch = curl_init($url);
curl_setopt_array($ch, [
CURLOPT_POST           => true,
CURLOPT_POSTFIELDS     => json_encode($params),
CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
CURLOPT_RETURNTRANSFER => true,
CURLOPT_TIMEOUT        => 10,
]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
}