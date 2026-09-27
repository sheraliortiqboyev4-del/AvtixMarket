<?php

function admin($user_id) {
global $connect;
$stmt = mysqli_prepare($connect, "SELECT * FROM adminlar WHERE foydalanuvchi_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
return mysqli_num_rows($result) > 0;
}


$options = [
[['text' => 'APP Boshqaruv','icon_custom_emoji_id' => "5879585266426973039",'web_app' => ['url' => 'https://sora.sheralidev.uz/admin/']],['text' => 'Majburiy obuna','icon_custom_emoji_id' => "5967432491684860012"]],
[['text' => 'Admin qoshish','icon_custom_emoji_id' => "5920090136627908485"],['text' => 'Admin ochirish','icon_custom_emoji_id' => "5922712343011135025"]],
[['text' => 'Xabar yuborish','icon_custom_emoji_id' => "5877540355187937244"],['text' => 'Statistika','icon_custom_emoji_id' => "5931472654660800739"]],
[['text' => 'Panelni yopish','icon_custom_emoji_id' => "5879896690210639947"]],
];
$panel = json_encode(['keyboard' => $options, 'resize_keyboard' => true, 'selective' => true]);



$options = [
[['text' => 'Public kanal qoshish','icon_custom_emoji_id' => "5771868281212245617"],['text' => 'Public kanal ochirish','icon_custom_emoji_id' => "5771511103141975115"]],
[['text' => 'Zayafka kanal qoshish','icon_custom_emoji_id' => "5886473311637999700"],['text' => 'Zayafka kanal ochirish','icon_custom_emoji_id' => "5884050696679986441"]],
[['text' => 'Bot qoshish','icon_custom_emoji_id' => "5931614414351372818"],['text' => 'Bot ochirish','icon_custom_emoji_id' => "5931415565955503486"]],
[['text' => 'Url qoshish','icon_custom_emoji_id' => "5879585266426973039"],['text' => 'Url ochirish','icon_custom_emoji_id' => "5985346521103604145"]],
[['text' => 'Orqaga','icon_custom_emoji_id' => "5879896690210639947"]],
];
$maj = json_encode(['keyboard' => $options, 'resize_keyboard' => true, 'selective' => true]);


if ($text == "Majburiy obuna" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><i>Kerakli hizmatlardan birini tanlashingiz mumkin!</i></b>",
'parse_mode' => "html",
'reply_markup' => $maj
]);
exit();
}



if ($text == "Url qoshish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Kanal linkini yuboring (Telegram, YouTube, Instagram va boshqalar):",
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
file_put_contents("step/$from_id.step", "link");
}

if (strpos($step, "link") === 0 && admin($from_id)) {
if ($text != "Bekor qilish") {
$invite_link = $text;
if (filter_var($invite_link, FILTER_VALIDATE_URL)) {
$result = mysqli_query($connect, "SELECT * FROM majburiysiz WHERE link = '$invite_link'");
$row = mysqli_fetch_assoc($result);
if ($row) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "$invite_link ushbu link allaqachon botga qo'shilgan!",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
}
mysqli_query($connect, "INSERT INTO majburiysiz (link) VALUES ('$invite_link')");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Url manzil qo'shildi (majburiy obuna emas)!: $invite_link",
'parse_mode' => 'html',
'disable_web_page_preview' => true,
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
} else {
file_put_contents("step/$from_id.step", "link");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Noto'g'ri URL formati. Iltimos, to'g'ri URL linkini yuboring."
]);
}
}
}

if ($text == "Public kanal qoshish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Kanalning ID sini yuboring:\n\nNamuna:\n<code>-1001234567890</code>",
'parse_mode' => 'html',
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
file_put_contents("step/$from_id.step", "public_kanal_id");
}

if (strpos($step, "public_kanal_id") === 0 && admin($from_id)) {
if ($text == "Bekor qilish") {
@unlink("step/$from_id.step");
@unlink("step/$from_id.txt");
$bot->sendMessage(['chat_id' => $from_id, 'text' => "Bekor qilindi.", 'reply_markup' => $panel]);
exit();
}

$input = trim($text);

if (!preg_match('/^-100\d+$/', $input)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "❌ Noto'g'ri format!\n\nID <code>-100</code> bilan boshlanishi kerak:\n\nNamuna:\n<code>-1001234567890</code>",
'parse_mode' => 'html'
]);
exit();
}

$result = mysqli_query($connect, "SELECT * FROM kanal WHERE chat_id = '$input'");
if (mysqli_num_rows($result) > 0) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "⚠️ Bu kanal allaqachon qo'shilgan!",
'reply_markup' => $panel
]);
@unlink("step/$from_id.step");
exit();
}

file_put_contents("step/$from_id.txt", $input);
file_put_contents("step/$from_id.step", "public_kanal_link");

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "✅ ID qabul qilindi: <code>$input</code>\n\nEndi kanalning URL havolasini yuboring:\n\nNamuna:\n<code>https://t.me/kanal_nomi</code>",
'parse_mode' => 'html',
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
exit();
}

if (strpos($step, "public_kanal_link") === 0 && admin($from_id)) {
if ($text == "Bekor qilish") {
@unlink("step/$from_id.step");
@unlink("step/$from_id.txt");
$bot->sendMessage(['chat_id' => $from_id, 'text' => "Bekor qilindi.", 'reply_markup' => $panel]);
exit();
}

$invite_link = trim($text);

if (!preg_match('/^https:\/\/.+/', $invite_link) || !filter_var($invite_link, FILTER_VALIDATE_URL)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "❌ Noto'g'ri URL!\n\nURL <code>https://</code> bilan boshlanishi kerak:\n\nNamuna:\n<code>https://t.me/kanal_nomi</code>",
'parse_mode' => 'html'
]);
exit();
}

$channel_id = trim(file_get_contents("step/$from_id.txt"));

if (!$channel_id) {
$bot->sendMessage(['chat_id' => $from_id, 'text' => "Xatolik! Qaytadan boshlang.", 'reply_markup' => $panel]);
@unlink("step/$from_id.step");
@unlink("step/$from_id.txt");
exit();
}
$invite_link_escaped = mysqli_real_escape_string($connect, $invite_link);
mysqli_query($connect, "INSERT INTO kanal (chat_id, kanal_url) VALUES ('$channel_id', '$invite_link_escaped')");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "✅ Kanal qo'shildi!\n\nChat ID: <code>$channel_id</code>\nHavola: $invite_link",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
@unlink("step/$from_id.step");
@unlink("step/$from_id.txt");
exit();
}




if ($text == "Bot qoshish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Iltimos, qo‘shmoqchi bo‘lgan bot tokenini yuboring:",
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
file_put_contents("step/$from_id.step", "await_bot_token");
}


if (strpos($step, "await_bot_token") === 0 && admin($from_id)) {
if ($text != "Bekor qilish") {
if (preg_match('/^\d+:[a-zA-Z0-9_-]+$/', $text)) {
$token = $text;
$url = "https://api.telegram.org/bot$token/getMe";
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['ok' => false];
if ($data['ok']) {
$bot_username = $data['result']['username'];
file_put_contents("step/$from_id-bot_token.txt", $token);
file_put_contents("step/$from_id-username.txt", $bot_username);

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Bot token tekshirildi.\n\nEndi iltimos bot linkini yuboring:\nMasalan: https://t.me/$bot_username",
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
file_put_contents("step/$from_id.step", "await_bot_link");
} else {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Noto‘g‘ri token. Iltimos, haqiqiy bot tokenini yuboring."
]);
}
} else {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Token formati noto‘g‘ri. Iltimos, to‘g‘ri formatda yuboring."
]);
}
} 
}

if (strpos($step, "await_bot_link") === 0 && admin($from_id)) {
if ($text != "Bekor qilish") {
$bot_link = trim($text);
$bot_token = trim(file_get_contents("step/$from_id-bot_token.txt"));
$bot_username = trim(file_get_contents("step/$from_id-username.txt"));

if (preg_match('/^https:\/\/t\.me\/[a-zA-Z0-9_]+(\?start(=[a-zA-Z0-9_-]*)?)?$/', $bot_link)) {
$result = mysqli_query($connect, "SELECT * FROM bots WHERE bot_link = '$bot_link'");
if (mysqli_num_rows($result) > 0) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "$bot_link ushbu bot allaqachon mavjud!",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
} else {
mysqli_query($connect, "INSERT INTO bots (bot_tokenn, bot_link) VALUES ('$bot_token', '$bot_link')");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Bot muvaffaqiyatli qo‘shildi:\nBot link: $bot_link",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
}
unlink("step/$from_id-bot_token.txt");
unlink("step/$from_id-username.txt");
unlink("step/$from_id.step");
} else {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Noto‘g‘ri link formati. Iltimos, quyidagi ko‘rinishda yuboring:\nhttps://t.me/username"
]);
}
}
}


if ($text == "Zayafka kanal qoshish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Kanal ID ni yuboring: ID ni aniqlashda @User_id_infobot dan foydalanishni tavsiya beramiz!\n\nNamuna: -1002272074461=https://t.me/+_TYXhIdvsw01MjQy",
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text' => "Bekor qilish"]]]
])
]);
file_put_contents("step/$from_id.step", "add_zayafka");
}

if (strpos($step, "add_zayafka") === 0 && admin($from_id)) {
if ($text != "Bekor qilish") {
$parts = explode('=', $text);
if (count($parts) == 2) {
$id = trim($parts[0]);
$linkk = trim($parts[1]);
$result = mysqli_query($connect, "SELECT * FROM zayafka WHERE invite_link = '$linkk'");
if (mysqli_num_rows($result) > 0) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "$linkk ushbu kanal allaqachon botga qo'shilgan!",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
}
mysqli_query($connect, "INSERT INTO zayafka (chat_id, invite_link) VALUES ('$id', '$linkk')");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Zayafka kanal qo'shildi va majburiy obuna sifatida belgilandi: $linkk",
'parse_mode' => 'html',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
} else {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Noto'g'ri format. Iltimos, namunadagidek yuboring.\n\nNamuna: -1002272074461=https://t.me/+_TYXhIdvsw01MjQy"
]);
file_put_contents("step/$from_id.step", "add_zayafka");
}
}
}



if ($text == "Zayafka kanal ochirish" && admin($from_id)) {
$result = mysqli_query($connect, "SELECT * FROM zayafka");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['invite_link'], 'callback_data' => 'delete_zayafka_' . $row['invite_link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "O'chirish uchun Zayafka URL ni tanlang:",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
}

if (strpos($data, 'delete_zayafka_') === 0) {
$linkk = str_replace('delete_zayafka_', '', $data);
mysqli_query($connect, "DELETE FROM zayafka WHERE invite_link = '$linkk'");
$result = mysqli_query($connect, "SELECT * FROM zayafka");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['invite_link'], 'callback_data' => 'delete_zayafka_' . $row['invite_link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->editMessageReplyMarkup([
'chat_id' => $from_id,
'message_id' => $message_id,
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "Zayafka URL muvaffaqiyatli o'chirildi!",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
} elseif ($data == 'cancel_action') {
$bot->deleteMessage([
'chat_id' => $from_id,
'message_id' => $message_id
]);
}

if ($text == "Bot ochirish" && admin($from_id)) {
$result = mysqli_query($connect, "SELECT * FROM bots");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['bot_link'], 'callback_data' => 'delete_bot_' . $row['bot_link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "O'chirish uchun bot URL manzilni ni tanlang:",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
}

if (strpos($data, 'delete_bot_') === 0) {
$linke = str_replace('delete_bot_', '', $data);
mysqli_query($connect, "DELETE FROM bots WHERE bot_link = '$linke'");
$result = mysqli_query($connect, "SELECT * FROM bots");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['bot_link'], 'callback_data' => 'delete_bot_' . $row['bot_link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->editMessageReplyMarkup([
'chat_id' => $from_id,
'message_id' => $message_id,
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "Bot URL muvaffaqiyatli o'chirildi!",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
} elseif ($data == 'cancel_action') {
$bot->deleteMessage([
'chat_id' => $from_id,
'message_id' => $message_id
]);
}

if ($text == "Public kanal ochirish" && admin($from_id)) {
$result = mysqli_query($connect, "SELECT * FROM kanal");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['kanal_url'], 'callback_data' => 'delete_chan_' . $row['kanal_url']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "O'chirish uchun kanal URL manzilni ni tanlang:",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
}

if (strpos($data, 'delete_chan_') === 0) {
$linkaa = str_replace('delete_chan_', '', $data);
mysqli_query($connect, "DELETE FROM kanal WHERE kanal_url = '$linkaa'");
$result = mysqli_query($connect, "SELECT * FROM kanal");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['kanal_url'], 'callback_data' => 'delete_chan_' . $row['kanal_url']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->editMessageReplyMarkup([
'chat_id' => $from_id,
'message_id' => $message_id,
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "Kanal URL muvaffaqiyatli o'chirildi!",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
} elseif ($data == 'cancel_action') {
$bot->deleteMessage([
'chat_id' => $from_id,
'message_id' => $message_id
]);
}

if ($text == "Url ochirish" && admin($from_id)) {
$result = mysqli_query($connect, "SELECT * FROM majburiysiz");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['link'], 'callback_data' => 'delete_lin_' . $row['link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "O'chirish uchun URL manzilni ni tanlang:",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
}

if (strpos($data, 'delete_lin_') === 0) {
$linkaa = str_replace('delete_lin_', '', $data);
mysqli_query($connect, "DELETE FROM majburiysiz WHERE link = '$linkaa'");
$result = mysqli_query($connect, "SELECT * FROM majburiysiz");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['link'], 'callback_data' => 'delete_lin_' . $row['link']]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action']];
$bot->editMessageReplyMarkup([
'chat_id' => $from_id,
'message_id' => $message_id,
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "URL Link muvaffaqiyatli o'chirildi!",
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
} elseif ($data == 'cancel_action') {
$bot->deleteMessage([
'chat_id' => $from_id,
'message_id' => $message_id
]);
}


if ($text == "Admin qoshish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5920090136627908485'>➕</tg-emoji> Yangi admin ID ni yuboring:",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'resize_keyboard' => true,
'keyboard' => [[['text'=>"Orqaga",'icon_custom_emoji_id' => "5888484185261216745"]]]
])
]);
file_put_contents("step/$from_id.step", "addmin");
}

if (strpos($step, "addmin") === 0 && admin($from_id)) {
if ($text != "Orqaga") {
if (is_numeric($text)) {
$id = $text;
$result = mysqli_query($connect, "SELECT * FROM adminlar WHERE foydalanuvchi_id = '$id'");
if (mysqli_num_rows($result) > 0) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5771887475421090729'>👤</tg-emoji> $id ushbu ID raqamli foydalanuvchi hozirda botda admin!",
'parse_mode' => 'HTML',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
}
mysqli_query($connect, "INSERT INTO adminlar (foydalanuvchi_id) VALUES ('$id')");
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5771887475421090729'>👤</tg-emoji> $id raqamli foydalanuvchi botda admin qilib belgilandi!",
'parse_mode' => 'HTML',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
}
}
}


if ($text == "Admin ochirish" && admin($from_id)) {
$result = mysqli_query($connect, "SELECT * FROM adminlar");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['foydalanuvchi_id'], 'callback_data' => 'delete_admin_' . $row['foydalanuvchi_id'],'icon_custom_emoji_id' => "5922712343011135025"]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action','icon_custom_emoji_id' => "5879937509579820068"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5879896690210639947'>🗑</tg-emoji> O'chirish uchun admin ID ni tanlang:",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
}

if (strpos($data, 'delete_admin_') === 0) {
$admin_id = str_replace('delete_admin_', '', $data);
mysqli_query($connect, "DELETE FROM adminlar WHERE foydalanuvchi_id = '$admin_id'");
$result = mysqli_query($connect, "SELECT * FROM adminlar");
$keyboard = [];
while ($row = mysqli_fetch_assoc($result)) {
$keyboard[] = [
['text' => $row['foydalanuvchi_id'], 'callback_data' => 'delete_admin_' . $row['foydalanuvchi_id'],'icon_custom_emoji_id' => "5922712343011135025"]
];
}
$keyboard[] = [['text' => "Bekor qilish", 'callback_data' => 'cancel_action','icon_custom_emoji_id' => "5879937509579820068"]];
$bot->editMessageReplyMarkup([
'chat_id' => $from_id,
'message_id' => $message_id,
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5879896690210639947'>🗑</tg-emoji> Admin muvaffaqiyatli o'chirildi!",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $keyboard
])
]);
} elseif ($data == 'cancel_action') {
$bot->deleteMessage([
'chat_id' => $from_id,
'message_id' => $message_id
]);
}






if ($text === "/admin" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => '<tg-emoji emoji-id="5877318502947229960">💻</tg-emoji> Admin panelga hush kelibsiz!',
'parse_mode' => 'HTML',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit();
}

if ($text == "Orqaga" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => '<tg-emoji emoji-id="5877318502947229960">💻</tg-emoji> Admin panelga hush kelibsiz!',
'parse_mode' => 'html',
'reply_markup' => $panel
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
}

if ($text == "Bekor qilish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => '<tg-emoji emoji-id="5877318502947229960">💻</tg-emoji> Amaliyot bekor qilindi!',
'parse_mode' => 'html',
'reply_markup' => $maj
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit;
}

if ($text == "Panelni yopish" && admin($from_id)) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => '<tg-emoji emoji-id="5841541824803509441">🗑</tg-emoji> Panel yopildi!',
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['remove_keyboard' => true])
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit();
}


if ($text == "Statistika" && admin($from_id)) {

$result = mysqli_query($connect, "SELECT COUNT(*) as total FROM users");
$row = mysqli_fetch_assoc($result);
$total_users = $row['total'];
    
$today = date('Y-m-d');
$result_today = mysqli_query($connect, "SELECT COUNT(*) as today FROM users WHERE DATE(sana) = '$today'");
$row_today = mysqli_fetch_assoc($result_today);
$today_users = $row_today['today'] ?? 0;

$text_stat = "<tg-emoji emoji-id='5931472654660800739'>📊</tg-emoji> <b>Bot Statistika</b>\n\n";
$text_stat .= "<tg-emoji emoji-id='5942877472163892475'>👥</tg-emoji> Jami obunachilar: <b>$total_users</b>\n";
$text_stat .= "<tg-emoji emoji-id='5900006938271288826'>2️⃣</tg-emoji> Bugun qo'shilgan: <b>$today_users</b>\n";

$bot->sendMessage([
'chat_id' => $from_id,
'text' => $text_stat,
'parse_mode' => 'HTML'
]);
unlink("step/$from_id.txt");
unlink("step/$from_id.step");
exit();
}




$step_file = "step/$from_id.step";
$current_step = file_exists($step_file) ? file_get_contents($step_file) : '';

if ($text == "Xabar yuborish" && admin($from_id)) {
$check_active = mysqli_query($connect, "SELECT * FROM `sendusers` WHERE `status` = 'active'");
if (mysqli_num_rows($check_active) > 0) {
$row = mysqli_fetch_assoc($check_active);
$sent = $row['send'];
$not_sent = $row['nosend'];
$total = $sent + $not_sent;

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><tg-emoji emoji-id='5408910404732595664'>🕐</tg-emoji> Xabar yuborish davom etmoqda!</b>\n\n"
. "<tg-emoji emoji-id='5954224165874569584'>💬</tg-emoji> Yuborildi: <b>$sent</b>\n"
. "<tg-emoji emoji-id='5886496611835581345'>👤</tg-emoji> Yuborilmadi: <b>$not_sent</b>\n"
. "<tg-emoji emoji-id='5931472654660800739'>📊</tg-emoji> Jami: <b>$total</b>\n\n"
. "Nima qilmoqchisiz?",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'keyboard' => [
[['text' => 'Bekor qilish va yangisini yuborish','icon_custom_emoji_id' => "5408832111773757273"]],
[['text'=>"Orqaga",'icon_custom_emoji_id' => "5888484185261216745"]]
],
'resize_keyboard' => true
])
]);
exit;
}

file_put_contents($step_file, 'user_send');
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<i>Yuboriladigan xabarni kiriting!\n\nMasalan: Matn, Video, Rasm, Musiqa, Audio, Tugmali xabar...</i>",
'parse_mode' => 'HTML',
'reply_markup'=>json_encode([
'resize_keyboard'=>true,
'keyboard'=>[[['text'=>"Orqaga",'icon_custom_emoji_id' => "5888484185261216745"]]]
])
]);
exit;
}

if ($text == "Bekor qilish va yangisini yuborish" && admin($from_id)) {
mysqli_query($connect, "UPDATE `sendusers` SET `status` = 'cancelled' WHERE `status` = 'active'");

file_put_contents($step_file, 'user_send');
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<i><tg-emoji emoji-id='5409029658794537988'>✅</tg-emoji> Oldingi yuborish bekor qilindi.\n\nEndi yangi xabarni kiriting:</i>",
'parse_mode' => 'html',
'reply_markup' => json_encode([
'keyboard' => [[['text'=>"Orqaga",'icon_custom_emoji_id' => "5888484185261216745"]]],
'resize_keyboard' => true
])
]);
exit;
}

if ($current_step === 'user_send') {
if ($text === 'Orqaga') {
if (file_exists($step_file)) unlink($step_file);
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "Bosh menyu",
'reply_markup' => $panel
]);
exit;
}

$check_active = mysqli_query($connect, "SELECT * FROM `sendusers` WHERE `status` = 'active'");
if (mysqli_num_rows($check_active) > 0) {
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<i><tg-emoji emoji-id='5408910404732595664'>🕐</tg-emoji> Xabar yuborish davom etmoqda. Yuborish jarayoni yakunlanishini kuting!</i>",
'parse_mode' => 'HTML',
'reply_markup' => $panel
]);
if (file_exists($step_file)) unlink($step_file);
exit;
}

$vt = date('H:i', strtotime("+1 minutes"));
$soatm = date('H:i');

$tugmaa = !empty($update->message->reply_markup) ? json_encode($update->message->reply_markup) : null;
$reply_markup = $tugmaa ? base64_encode($tugmaa) : null;

$unique_id = $message_id . "_user";

$result = mysqli_query($connect, "SELECT * FROM `sendusers` WHERE `unique_id` = '$unique_id'");
if (mysqli_num_rows($result) > 0) {
mysqli_query($connect, "UPDATE `sendusers` SET 
`mid` = '$message_id', 
`boshlash_vaqt` = '$soatm', 
`button` = '$reply_markup', 
`soni` = '0', 
`joriy_vaqt` = '$vt', 
`status` = 'active', 
`send` = '0', 
`holat` = 'copyMessage', 
`nosend` = '0'
WHERE `unique_id` = '$unique_id'"
);
} else {
mysqli_query($connect, "INSERT INTO `sendusers` 
(`unique_id`, `mid`, `boshlash_vaqt`, `soni`, `joriy_vaqt`, `status`, `send`, `holat`, `nosend`, `button`) 
VALUES ('$unique_id', '$message_id', '$soatm', 0, '$vt', 'active', 0, 'copyMessage', 0, '$reply_markup')"
);
}
$bot->sendMessage([
'chat_id' => $from_id,
'text'=>"<i><tg-emoji emoji-id='5954224165874569584'>💬</tg-emoji> Yuboryapman yuborish vaqti $vt</i>",
'parse_mode' => 'HTML',
'reply_markup' => $panel
]);
if (file_exists($step_file)) unlink($step_file);
exit;
}