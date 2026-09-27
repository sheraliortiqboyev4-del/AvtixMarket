<?php
date_default_timezone_set('Asia/Tashkent');

require_once("config/config.php");
require_once("config/function.php");
require_once("config/username_info.php");

$bot = new Begzod();
$update = $bot->update();


$sana = date("Y-m-d H:i:s");
function GenerateNum($length = 10){
return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))),1,$length);
}


if (!empty($update->message)) {
$msg = $update->message;
$chat_type = $msg->chat->type;
$chat_id = $msg->chat->id;
$from_id = $msg->from->id;
$text = $msg->text ?? '';
$first_name = $msg->from->first_name ?? '';
$username = $msg->from->username ?? 'user_' . $from_id;
$message_id = $msg->message_id;
}


if (isset($update->callback_query)) {
$from_id = $update->callback_query->from->id;
$first_name = $update->callback_query->from->first_name;
$data = $update->callback_query->data;
$chat_id = $update->callback_query->message->chat->id ?? $from_id;
$chat_type = $update->callback_query->message->chat->type;
$message_id = $update->callback_query->message->message_id ?? 0;
}




if (isset($update->chat_member)) {
$chat_id_member = $update->chat_member->chat->id;
$user_id = $update->chat_member->from->id;
$new_status = $update->chat_member->new_chat_member->status;

if ($new_status == 'member' || $new_status == 'administrator' || $new_status == 'creator') {
if (file_exists("step/$user_id.txt")) {
Tugma_Edit($user_id, $bot);
}
} elseif ($new_status == 'left' || $new_status == 'kicked' || $new_status == 'banned') {

$stmt = mysqli_prepare($connect, "DELETE FROM joinRequest WHERE user_id = ? AND chat_id = ?");
mysqli_stmt_bind_param($stmt, "is", $user_id, $chat_id_member);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$stmt2 = mysqli_prepare($connect, "SELECT * FROM kanal WHERE chat_id = ?");
mysqli_stmt_bind_param($stmt2, "s", $chat_id_member);
mysqli_stmt_execute($stmt2);
$res2 = mysqli_stmt_get_result($stmt2);
$is_our_channel = mysqli_num_rows($res2) > 0;
mysqli_stmt_close($stmt2);

$stmt3 = mysqli_prepare($connect, "SELECT * FROM zayafka WHERE chat_id = ?");
mysqli_stmt_bind_param($stmt3, "s", $chat_id_member);
mysqli_stmt_execute($stmt3);
$res3 = mysqli_stmt_get_result($stmt3);
$is_zayafka = mysqli_num_rows($res3) > 0;
mysqli_stmt_close($stmt3);

if ($is_our_channel || $is_zayafka) {
$old_msg = @file_get_contents("step/$user_id.txt");
if ($old_msg) {
$bot->deleteMessage(['chat_id' => $user_id, 'message_id' => $old_msg]);
@unlink("step/$user_id.txt");
}
majburiy($user_id, $bot, false);
}
}
}
$bot->setWebhook([
'url' => 'https://sora.sheralidev.uz/index.php',
'allowed_updates' => json_encode([
'message',
'callback_query', 
'chat_join_request',
'chat_member',
'channel_post'  // <-- shu qo'shilishi shart!
])
]);

if ($data == 'check_obuna') {
check($from_id, $bot, true);
}

if (isset($update->chat_join_request)) {
$chat_id = $update->chat_join_request->chat->id;
$user_id = $update->chat_join_request->from->id;
zayafka_qabul($user_id, $chat_id, 'pending');
Tugma_Edit($user_id, $bot);
}
if (!file_exists("step")) {
mkdir("step");
}
$step = file_get_contents("step/$from_id.step");
$message_stepp = file_get_contents("step/$from_id.txt");
$message_step = "step/$from_id.txt";


if ($text == "/start" || $text == "/start true") {
majburiy($from_id, $bot);
}

function majburiy($user_id, $bot, $edit_buttons = false) {
global $connect, $from_id;
    
$result_channels = mysqli_query($connect, "SELECT * FROM zayafka");
$channels = mysqli_fetch_all($result_channels, MYSQLI_ASSOC);
$result_public = mysqli_query($connect, "SELECT * FROM kanal");
$public_channels = mysqli_fetch_all($result_public, MYSQLI_ASSOC);
$result_bots = mysqli_query($connect, "SELECT bot_tokenn, bot_link FROM bots");
$bots = mysqli_fetch_all($result_bots, MYSQLI_ASSOC);
$result_majsz = mysqli_query($connect, "SELECT * FROM majburiysiz");
$majsiz = mysqli_fetch_all($result_majsz, MYSQLI_ASSOC);
    
$keyboard = [];
$all_subscribed = true;

foreach ($channels as $channel) {
$stmt = mysqli_prepare($connect, "SELECT status FROM joinRequest WHERE user_id = ? AND chat_id = ?");
mysqli_stmt_bind_param($stmt, "is", $user_id, $channel['chat_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$status = (mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res)['status'] : 'not_member';
mysqli_stmt_close($stmt);

if ($status != 'member' && $status != 'pending') {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channel['invite_link'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}

foreach ($public_channels as $channel) {
$member = $bot->getChatMember(["chat_id" => $channel['chat_id'], "user_id" => $user_id]);
$isMember = $member->result->status ?? 'left';
if ($isMember != "member" && $isMember != "administrator" && $isMember != "creator") {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channel['kanal_url'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}

foreach ($majsiz as $channell) {
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channell['link'], 'icon_custom_emoji_id' => "5778168620278354602"]];
}

foreach ($bots as $other_bot) {
$url = "https://api.telegram.org/bot" . $other_bot['bot_tokenn'] . "/sendchataction?chat_id=$user_id&action=typing";
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['ok' => false];
if (!$data['ok']) {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $other_bot['bot_link'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}
    
if (!$all_subscribed) {
$keyboard[] = [['text' => "Tasdiqlash", 'callback_data' => 'check_obuna', 'icon_custom_emoji_id' => "5408909562919007848"]];
   
if ($edit_buttons) {
$message_id_file = file_get_contents("step/$user_id.txt");
if ($message_id_file) {
$bot->editMessageReplyMarkup([
'chat_id'      => $user_id,
'message_id'   => $message_id_file,
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
}
} else {
$old_msg = @file_get_contents("step/$user_id.txt");
if ($old_msg) {
$bot->deleteMessage(['chat_id' => $user_id, 'message_id' => $old_msg]);
}
$res = $bot->sendMessage([
'chat_id'      => $user_id,
'text'         => "Botni ishlatish uchun quyidagi kanallarimizga va botlarga obuna bo'lishingiz kerak:",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
])->result->message_id;
file_put_contents("step/$user_id.txt", $res);
}
} else {
$old_msg = @file_get_contents("step/$user_id.txt");
if ($old_msg) {
$bot->deleteMessage(['chat_id' => $user_id, 'message_id' => $old_msg]);
}
$menu = [[['text' => "Stars olish", 'callback_data' => 'buy_stars','icon_custom_emoji_id' => "5363844691580177304"],['text' => "Premium olish", 'callback_data' => 'buy_premium','icon_custom_emoji_id' => "5388584703932522832"]],
[['text' => "Gift olish", 'callback_data' => 'buy_gift','icon_custom_emoji_id' => "5293983582472135373"],['text' => "Ton olish", 'callback_data' => 'buy_ton','icon_custom_emoji_id' => "5406976471153545018"]],
[['text' => "Statistikam", 'callback_data' => 'statistika','icon_custom_emoji_id' => "5936143551854285132"]],
[['text' => "Referal", 'callback_data' => 'ref','icon_custom_emoji_id' => "5409257566939134596"],['text' => "Profilim", 'callback_data' => 'profil','icon_custom_emoji_id' => "5409014699423446355"]]];


$bot->sendMessage([
'chat_id' => $user_id,
'text' => "<tg-emoji emoji-id='5994750571041525522'>👋</tg-emoji> <i><b><u>Assalom aleykum $first_name SoraPay botga xush kelibsiz!</u></b></i>

<tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Bot orqali quyidagilarni xarid qilish mumkin. «Tez va Xavfsiz»</i>
<blockquote expandable><tg-emoji emoji-id='5956148757899776734'>⭐️</tg-emoji>Telegram Stars - yulduzcha
<tg-emoji emoji-id='5956561749070057536'>⭐️</tg-emoji>Telegram Premium
<tg-emoji emoji-id='5875180111744995604'>🎁</tg-emoji>Telegram Gift sovg'alar
<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> TON coin</blockquote>

<b>Boshlash uchun xizmatni tanlang:</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $menu
])
]);
@unlink("step/$user_id.step");
@unlink("step/{$user_id}_quantity.txt");
@unlink("step/{$user_id}_month.txt");
@unlink("step/{$user_id}_custom_emoji.txt");
@unlink("step/{$user_id}_emoji.txt");
@unlink("step/{$user_id}_gift_id.txt");
@unlink("step/{$user_id}_uzs_price.txt");
@unlink("step/{$user_id}_uzs_summasi.txt");
@unlink("step/{$user_id}_quantity_ton.txt");
@unlink("step/$user_id.amount");
@unlink("step/$user_id.username");
@unlink("step/$user_id.txt");
}
}
function check($user_id, $bot, $is_confirm_action = false) {
global $connect;
$result = mysqli_query($connect, "SELECT * FROM zayafka");
$channels = mysqli_fetch_all($result, MYSQLI_ASSOC);
$result_public = mysqli_query($connect, "SELECT * FROM kanal");
$public_channels = mysqli_fetch_all($result_public, MYSQLI_ASSOC);
$result_bots = mysqli_query($connect, "SELECT bot_tokenn, bot_link FROM bots");
$bots = mysqli_fetch_all($result_bots, MYSQLI_ASSOC);
$all_subscribed = true;
foreach ($channels as $channel) {
$stmt = mysqli_prepare($connect, "SELECT status FROM joinRequest WHERE user_id = ? AND chat_id = ?");
mysqli_stmt_bind_param($stmt, "is", $user_id, $channel['chat_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$status = (mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res)['status'] : 'not_member';
mysqli_stmt_close($stmt);
if ($status != 'member' && $status != 'pending') {
$all_subscribed = false;
break;
}
}
if ($all_subscribed) {
foreach ($public_channels as $channel) {
$isMember = $bot->getChatMember(["chat_id" => $channel['chat_id'], "user_id" => $user_id])->result->status;
if ($isMember != "member" && $isMember != "administrator" && $isMember != "creator") {
$all_subscribed = false;
break;
}
}
}
foreach ($bots as $other_bot) {
$url = "https://api.telegram.org/bot" . $other_bot['bot_tokenn'] . "/sendchataction?chat_id=$user_id&action=typing";
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['ok' => false];
if (!$data['ok']) {
$all_subscribed = false;
break;
}
}
if ($all_subscribed && $is_confirm_action) {
$bot->deleteMessage([
'chat_id' => $user_id,
'message_id' => file_get_contents("step/$user_id.txt")
]);
$menu = [[['text' => "Stars olish", 'callback_data' => 'buy_stars','icon_custom_emoji_id' => "5363844691580177304"],['text' => "Premium olish", 'callback_data' => 'buy_premium','icon_custom_emoji_id' => "5388584703932522832"]],
[['text' => "Gift olish", 'callback_data' => 'buy_gift','icon_custom_emoji_id' => "5293983582472135373"],['text' => "Ton olish", 'callback_data' => 'buy_ton','icon_custom_emoji_id' => "5406976471153545018"]],
[['text' => "Statistikam", 'callback_data' => 'statistika','icon_custom_emoji_id' => "5936143551854285132"]],
[['text' => "Referal", 'callback_data' => 'ref','icon_custom_emoji_id' => "5409257566939134596"],['text' => "Profilim", 'callback_data' => 'profil','icon_custom_emoji_id' => "5409014699423446355"]]];


$bot->sendMessage([
'chat_id' => $user_id,
'text' => "<tg-emoji emoji-id='5994750571041525522'>👋</tg-emoji> <i><b><u>Assalom aleykum $first_name SoraPay botga xush kelibsiz!</u></b></i>

<tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Bot orqali quyidagilarni xarid qilish mumkin. «Tez va Xavfsiz»</i>
<blockquote expandable><tg-emoji emoji-id='5956148757899776734'>⭐️</tg-emoji>Telegram Stars - yulduzcha
<tg-emoji emoji-id='5956561749070057536'>⭐️</tg-emoji>Telegram Premium
<tg-emoji emoji-id='5875180111744995604'>🎁</tg-emoji>Telegram Gift sovg'alar
<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> TON coin</blockquote>

<b>Boshlash uchun xizmatni tanlang:</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $menu
])
]);
@unlink("step/$user_id.step");
@unlink("step/{$user_id}_quantity.txt");
@unlink("step/{$user_id}_month.txt");
@unlink("step/{$user_id}_custom_emoji.txt");
@unlink("step/{$user_id}_emoji.txt");
@unlink("step/{$user_id}_gift_id.txt");
@unlink("step/{$user_id}_uzs_price.txt");
@unlink("step/{$user_id}_uzs_summasi.txt");
@unlink("step/{$user_id}_quantity_ton.txt");
@unlink("step/$user_id.amount");
@unlink("step/$user_id.username");
@unlink("step/$user_id.txt");
exit();
}
if (!$all_subscribed) {
$bot->answerCallbackQuery([
'callback_query_id' => $bot->update()->callback_query->id,
'text' => "Siz hali barcha kanallarga yoki botlarga obuna bo'lmagansiz!",
'show_alert' => true
]);
Tugma_Edit($user_id, $bot);
}
return $all_subscribed;
}
function zayafka_qabul($user_id, $chat_id, $status) {
global $connect;
$stmt = mysqli_prepare($connect, "INSERT INTO joinRequest (user_id, chat_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
mysqli_stmt_bind_param($stmt, "isss", $user_id, $chat_id, $status, $status);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
}
function Tugma_Edit($user_id, $bot) {
global $connect;
    
$result_channels = mysqli_query($connect, "SELECT * FROM zayafka");
$channels = mysqli_fetch_all($result_channels, MYSQLI_ASSOC);
$result_public = mysqli_query($connect, "SELECT * FROM kanal");
$public_channels = mysqli_fetch_all($result_public, MYSQLI_ASSOC);
$result_bots = mysqli_query($connect, "SELECT bot_tokenn, bot_link FROM bots");
$bots = mysqli_fetch_all($result_bots, MYSQLI_ASSOC);
$result_majsz = mysqli_query($connect, "SELECT * FROM majburiysiz");
$majsiz = mysqli_fetch_all($result_majsz, MYSQLI_ASSOC);
    
$keyboard = [];
$all_subscribed = true;

foreach ($channels as $channel) {
$stmt = mysqli_prepare($connect, "SELECT status FROM joinRequest WHERE user_id = ? AND chat_id = ?");
mysqli_stmt_bind_param($stmt, "is", $user_id, $channel['chat_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$status = (mysqli_num_rows($res) > 0) ? mysqli_fetch_assoc($res)['status'] : 'not_member';
mysqli_stmt_close($stmt);

if ($status != 'member' && $status != 'pending') {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channel['invite_link'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}

foreach ($public_channels as $channel) {
$isMember = $bot->getChatMember(["chat_id" => $channel['chat_id'], "user_id" => $user_id])->result->status;
if ($isMember != "member" && $isMember != "administrator" && $isMember != "creator") {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channel['kanal_url'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}

foreach ($majsiz as $channell) {
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $channell['link'], 'icon_custom_emoji_id' => "5778168620278354602"]];
}

foreach ($bots as $other_bot) {
$url = "https://api.telegram.org/bot" . $other_bot['bot_tokenn'] . "/sendchataction?chat_id=$user_id&action=typing";
$response = @file_get_contents($url);
$data = $response ? json_decode($response, true) : ['ok' => false];
if (!$data['ok']) {
$all_subscribed = false;
$keyboard[] = [['text' => "Obuna bo'lish", 'url' => $other_bot['bot_link'], 'icon_custom_emoji_id' => "5409380965644514142"]];
}
}
    
$keyboard[] = [['text' => "Tasdiqlash", 'callback_data' => 'check_obuna', 'icon_custom_emoji_id' => "5408909562919007848"]];
    
$message_id = file_get_contents("step/$user_id.txt");
if ($message_id) {
$bot->editMessageReplyMarkup([
'chat_id'      => $user_id,
'message_id'   => $message_id,
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
}

if ($all_subscribed) {
$bot->deleteMessage(['chat_id' => $user_id, 'message_id' => $message_id]);
$menu = [[['text' => "Stars olish", 'callback_data' => 'buy_stars','icon_custom_emoji_id' => "5363844691580177304"],['text' => "Premium olish", 'callback_data' => 'buy_premium','icon_custom_emoji_id' => "5388584703932522832"]],
[['text' => "Gift olish", 'callback_data' => 'buy_gift','icon_custom_emoji_id' => "5293983582472135373"],['text' => "Ton olish", 'callback_data' => 'buy_ton','icon_custom_emoji_id' => "5406976471153545018"]],
[['text' => "Statistikam", 'callback_data' => 'statistika','icon_custom_emoji_id' => "5936143551854285132"]],
[['text' => "Referal", 'callback_data' => 'ref','icon_custom_emoji_id' => "5409257566939134596"],['text' => "Profilim", 'callback_data' => 'profil','icon_custom_emoji_id' => "5409014699423446355"]]];


$bot->sendMessage([
'chat_id' => $user_id,
'text' => "<tg-emoji emoji-id='5994750571041525522'>👋</tg-emoji> <i><b><u>Assalom aleykum $first_name SoraPay botga xush kelibsiz!</u></b></i>

<tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Bot orqali quyidagilarni xarid qilish mumkin. «Tez va Xavfsiz»</i>
<blockquote expandable><tg-emoji emoji-id='5956148757899776734'>⭐️</tg-emoji>Telegram Stars - yulduzcha
<tg-emoji emoji-id='5956561749070057536'>⭐️</tg-emoji>Telegram Premium
<tg-emoji emoji-id='5875180111744995604'>🎁</tg-emoji>Telegram Gift sovg'alar
<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> TON coin</blockquote>

<b>Boshlash uchun xizmatni tanlang:</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $menu
])
]);
@unlink("step/$user_id.step");
@unlink("step/{$user_id}_quantity.txt");
@unlink("step/{$user_id}_month.txt");
@unlink("step/{$user_id}_custom_emoji.txt");
@unlink("step/{$user_id}_emoji.txt");
@unlink("step/{$user_id}_gift_id.txt");
@unlink("step/{$user_id}_uzs_price.txt");
@unlink("step/{$user_id}_uzs_summasi.txt");
@unlink("step/{$user_id}_quantity_ton.txt");
@unlink("step/$user_id.amount");
@unlink("step/$user_id.username");
@unlink("step/$user_id.txt");
}
}






// ================= /START REF =================
if(mb_stripos($text,"/start ") !== false){
$ref_id = explode(" ", $text)[1];

$ref_check = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM users WHERE user_ref_id='$ref_id'"));
if(!$ref_check){
exit();
}

$user_ref_id = $ref_check['user_ref_id'];
$self = mysqli_fetch_assoc(mysqli_query($connect,"SELECT user_ref_id FROM users WHERE user_id='$from_id'"))['user_ref_id'];
if($self == $user_ref_id){
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Referal bilan o'zingizni taklif qila olmaysiz. Qayta /start bosing</b>",
'parse_mode'=>'HTML'
]);
exit();
}
$check = mysqli_query($connect,"SELECT * FROM users WHERE user_id='$from_id'");
if(mysqli_num_rows($check) == 0){

$a = rand(1,9);
$b = rand(1,9);
$captcha = $a + $b;

$fake1 = $captcha + rand(1,3);
$fake2 = max(1, $captcha - rand(1,3));

$options = [$captcha, $fake1, $fake2];
shuffle($options);

$pass = GenerateNum(10);
$time = time();

mysqli_query($connect,"INSERT INTO users SET 
user_id='$from_id',
first_name='$first_name',
username='$username',
ref_id='$user_ref_id',
user_ref_id='$pass',
captcha='$captcha',
captcha_required='1',
captcha_passed='0',
captcha_try='0',
captcha_time='$time',
balance='0',
ref='0',
sana='$sana'
");

$keyboard = [
'inline_keyboard'=>[
[
['text'=>$options[0],'callback_data'=>"captcha_".$options[0]],
['text'=>$options[1],'callback_data'=>"captcha_".$options[1]],
['text'=>$options[2],'callback_data'=>"captcha_".$options[2]],
]
]
];

$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5258113901106580375'>⌛️</tg-emoji> <b>Iltimos captchani yeching!</b>\n\n$a + $b = ?",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode($keyboard)
]);
exit();
}else{
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Siz allaqachon botdasiz!</b>",
'parse_mode'=>'HTML'
]);
exit();
}
}

if (isset($chat_type) && $chat_type == 'private') {
$a = mysqli_fetch_assoc(mysqli_query($connect, "SELECT * FROM users WHERE user_id = '$from_id'"));
if (!$a) {
$pass = GenerateNum(10);
mysqli_query($connect,"INSERT INTO users SET 
user_id='$from_id',
first_name='$first_name',
username='$username',
user_ref_id='$pass',
captcha_required='0',
captcha_passed='1',
balance='0',
ref='0',
sana='$sana'
");
}
}





$payme_merchant_id = ""; // Payme Merchant ID
$ton_wallet_address = "ABE1LD2v"; // Sizning TON hamyoningiz

$premium_3 = "6.70"; // Fragmentdagi 3oylik premium narxi foyda bilan qo'yish kerak (0.2 qo'shamiz)
$premium_6 = "8.92"; // Fragmentdagi 6oylik premium narxi foyda bilan qo'yish kerak (0.2 qo'shamiz)
$premium_12 = "16.16"; // Fragmentdagi 12oylik premium narxi foyda bilan qo'yish kerak (0.2 qo'shamiz)

$stars_50 = "0.4279"; // Fragmentdagi 50ta stars narxi foyda bilan qoyish kerak (0.3 qo'shamiz)


$url = "https://sora.sheralidev.uz/BuyTon/price_ton_uzs.txt";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$content = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// SENING REAL XARID NARXING (masalan DB dan olasan)
$buy_price = 21000;  

// Minimal sotish narx (foyda bilan)
$minimal_price = $buy_price + 1500;

if ($content !== false && $http_code == 200) {
$ton_price = floatval(trim($content));
$final_price = max($ton_price + 1500, $minimal_price);

} else {
$final_price = $minimal_price; // fallback
}


$balance_file = "https://sora.sheralidev.uz/BuyStars/balance.txt";
$content = @file_get_contents($balance_file);
if ($content === false) {
return 0;
}
$balance = floatval(trim($content));
$per_unit = $stars_50 / 50;

$olish_mumkin = floor($balance / $per_unit);
$narx = floor($stars_50 * $final_price);

$narx_premium_3 = floor($premium_3 * $final_price);
$narx_premium_6 = floor($premium_6 * $final_price);
$narx_premium_12 = floor($premium_12 * $final_price);

$available = [];
if ($balance >= $premium_3) {
$available[] = 3;
}
if ($balance >= $premium_6) {
$available[] = 6;
}
if ($balance >= $premium_12) {
$available[] = 12;
}

$MyStarBalance = json_decode(file_get_contents("https://api.telegram.org/bot".BOT_TOKEN."/getMyStarBalance"), true);
$Mystars = $MyStarBalance['result']['amount'];

$urls = "https://sora.sheralidev.uz/Gift_card/stars_balance.json";
$jsons = file_get_contents($urls);
$da = json_decode($jsons, true);
$MyProfilStars = isset($da['balance']) ? (int)$da['balance'] : 0;




$admin_url = "https://sora.sheralidev.uz/admin/?initData=" . urlencode($update->message->web_app_data->data ?? '');

$menu = [[['text' => "Stars olish", 'callback_data' => 'buy_stars','icon_custom_emoji_id' => "5363844691580177304"],['text' => "Premium olish", 'callback_data' => 'buy_premium','icon_custom_emoji_id' => "5388584703932522832"]],
[['text' => "Gift olish", 'callback_data' => 'buy_gift','icon_custom_emoji_id' => "5293983582472135373"],['text' => "Ton olish", 'callback_data' => 'buy_ton','icon_custom_emoji_id' => "5406976471153545018"]],
[['text' => "Statistikam", 'callback_data' => 'statistika','icon_custom_emoji_id' => "5936143551854285132"]],
[['text' => "Referal", 'callback_data' => 'ref','icon_custom_emoji_id' => "5409257566939134596"],['text' => "Profilim", 'callback_data' => 'profil','icon_custom_emoji_id' => "5409014699423446355"]]];


if (isset($data) && $data === "back_to_main") {
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5994750571041525522'>👋</tg-emoji> <i><b><u>Assalom aleykum $first_name SoraPay botga xush kelibsiz!</u></b></i>

<tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Bot orqali quyidagilarni xarid qilish mumkin. «Tez va Xavfsiz»</i>
<blockquote expandable><tg-emoji emoji-id='5956148757899776734'>⭐️</tg-emoji>Telegram Stars - yulduzcha
<tg-emoji emoji-id='5956561749070057536'>⭐️</tg-emoji>Telegram Premium
<tg-emoji emoji-id='5875180111744995604'>🎁</tg-emoji>Telegram Gift sovg'alar
<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> TON coin</blockquote>

<b>Boshlash uchun xizmatni tanlang:</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode([
'inline_keyboard' => $menu
])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}










// ====================== BUY STARS ======================

if ((isset($data) && $data === "buy_stars") || $text == "/stars") {
$is_subscribed = check($from_id, $bot, false);
if (!$is_subscribed) {
majburiy($from_id, $bot, false);
exit();
}
$stars_list = [50, 75, 100, 150, 250, 350, 500, 750, 1000];
$keyboard = [];
$row = [];
$olish_mumkin = $olish_mumkin;
$narx = $narx; 
$price_per_1 = $narx / 50;
foreach ($stars_list as $stars) {
$uzs_price = floor($price_per_1 * $stars);
$btn_text = "$stars - " . number_format($uzs_price) . " so‘m";
$row[] = ['text' => $btn_text, 'callback_data' => "stars_$stars",'icon_custom_emoji_id' => "5363844691580177304"];
if (count($row) == 2) {
$keyboard[] = $row;
$row = [];
}
}
if (!empty($row)) {
$keyboard[] = $row;
}
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5363844691580177304'>💛</tg-emoji> <b>Telegram Stars</b>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>STARS NEGA KERAK?</b>
<blockquote expandable><tg-emoji emoji-id='5769403330761593044'>👛</tg-emoji> 1- Reklama va xizmatlar — botlarda reklama yoki maxsus funksiyalarni ochish

<tg-emoji emoji-id='5775949822993371030'>🖼</tg-emoji> 2- Kontent sotib olish — kanal yoki bot ichidagi premium kontent uchun

<tg-emoji emoji-id='5846008814129649022'>👥</tg-emoji> 3- Yaratuvchilarni qo‘llab-quvvatlash — adminlarga pul/Stars yuborish

<tg-emoji emoji-id='5909201569898827582'>🔔</tg-emoji> 4- Ilova ichida to‘lov qilish — botlar va mini-applarda xizmatlarni sotib olish uchun

<tg-emoji emoji-id='6039859895291877126'>💎</tg-emoji> 5- Xavfsiz to‘lov — bank kartasiz, Telegram ichida to‘lov qilish</blockquote>
<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Cheklovlar</b>
<blockquote expandable>▫️<b>Minimal:</b> 50 ta
▫️<b>Maksimal:</b> " . number_format($olish_mumkin) . " ta</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni tanlang yoki raqam bilan yuboring <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji></b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
file_put_contents("step/$from_id.step", "stars_miqdor");
exit();
}

if (file_get_contents("step/$from_id.step") == 'stars_miqdor' && is_numeric(trim($text))) {
$quantity = intval(trim($text));
$max_stars = $olish_mumkin;
$price_per_1 = $narx / 50;
if ($quantity < 50) {
$stars_list = [50, 75, 100, 150, 250, 350, 500, 750, 1000];
$keyboard = [];
$row = [];
$olish_mumkin = $olish_mumkin;
$narx = $narx; 
$price_per_1 = $narx / 50;
foreach ($stars_list as $stars) {
$uzs_price = floor($price_per_1 * $stars);
$btn_text = "$stars - " . number_format($uzs_price) . " so‘m";
$row[] = ['text' => $btn_text, 'callback_data' => "stars_$stars",'icon_custom_emoji_id' => "5363844691580177304"];
if (count($row) == 2) {
$keyboard[] = $row;
$row = [];
}
}
if (!empty($row)) {
$keyboard[] = $row;
}
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5447644880824181073'>⚠️</tg-emoji> <b>Cheklovga amal qiling:</b>  

<blockquote>▫️<b> Minimal:</b> 50 ta
▫️<b> Maksimal:</b> " . number_format($olish_mumkin) . " ta</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni tanlang yoki raqam bilan yuboring <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji></b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
if ($quantity > $max_stars) {
$stars_list = [50, 75, 100, 150, 250, 350, 500, 750, 1000];
$keyboard = [];
$row = [];
$olish_mumkin = $olish_mumkin;
$narx = $narx; 
$price_per_1 = $narx / 50;
foreach ($stars_list as $stars) {
$uzs_price = floor($price_per_1 * $stars);
$btn_text = "$stars - " . number_format($uzs_price) . " so‘m";
$row[] = ['text' => $btn_text, 'callback_data' => "stars_$stars",'icon_custom_emoji_id' => "5363844691580177304"];
if (count($row) == 2) {
$keyboard[] = $row;
$row = [];
}
}
if (!empty($row)) {
$keyboard[] = $row;
}
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Cheklovga amal qiling:</b>  

<blockquote>▫️<b> Minimal:</b> 50 ta
▫️<b> Maksimal:</b> " . number_format($olish_mumkin) . " ta</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni tanlang yoki raqam bilan yuboring <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji></b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$uzs_needed = floor($price_per_1 * $quantity);
file_put_contents("step/$from_id.step", "username_stars");
file_put_contents("step/{$from_id}_quantity.txt", $quantity);
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchi nomi?</b>

<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Telegram Starslarni yuborishdan oldin, qaysi profilga yuborilishini aniq belgilab olishimiz kerak.</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}

if (isset($data) && strpos($data, "stars_") === 0) {
$quantity = (int) str_replace("stars_", "", $data);
$max_stars = $olish_mumkin;
$price_per_1 = $narx / 50;
$uzs_needed = floor($price_per_1 * $quantity);
if ($quantity > $max_stars) {
$bot->answerCallbackQuery([
'callback_query_id' => $update->callback_query->id,
'text' => "⚠️ Siz maksimal " . number_format($max_stars) . " ta stars olishingiz mumkin!",
'show_alert' => false,
'cache_time' => 20
]);
exit();
}
file_put_contents("step/$from_id.step", "username_stars");
file_put_contents("step/{$from_id}_quantity.txt", $quantity);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchi nomi?</b>

<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Telegram Starslarni yuborishdan oldin, qaysi profilga yuborilishini aniq belgilab olishimiz kerak.</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}

if (file_get_contents("step/$from_id.step") == "username_stars" && !empty($text)) {
$quantity = intval(@file_get_contents("step/{$from_id}_quantity.txt"));
$username_target = trim(str_replace("@", "", $text));
$username_input = trim($text);
if (strpos($username_input, "@") !== 0) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi @ bilan boshlanishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);    
exit();
}

if (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $username_target)) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi kamida 4 ta belgi bo'lishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$result = chekusername($username_target);
if (!$result['exists']) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Qabul qiluvchi topilmadi!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$price_per_1 = $narx / 50;
$amount = floor($price_per_1 * $quantity);
$turi = "stars";
// TON miqdorini hisoblash (webhook da tekshirish uchun saqlash kerak)
$ton_payment_amount = round($amount / $final_price, 6);
$insert_order_sql = "INSERT INTO orders (amount, status, user_id, username, quantity, turi, ton_amount) VALUES (?, 'pending', ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $insert_order_sql);
mysqli_stmt_bind_param($stmt,"disisd",$amount,$from_id,$username_target,$quantity,$turi,$ton_payment_amount);
mysqli_stmt_execute($stmt);
$new_id = mysqli_insert_id($connect);
$order_id = 'SORA-' . $new_id;
$update_sql = "UPDATE orders SET order_id = ? WHERE id = ?";
$update_stmt = mysqli_prepare($connect, $update_sql);
mysqli_stmt_bind_param($update_stmt, "si", $order_id, $new_id);
mysqli_stmt_execute($update_stmt);
$amount_tiyn = $amount * 100;
$params_array = ["m={$payme_merchant_id}","ac.order_id={$order_id}","ac.user_id={$from_id}","a={$amount_tiyn}","l=uz","cr=UZS","ct=15000"];
$params_string = implode(';', $params_array);
$base64_params = base64_encode($params_string);
$payme_url = "https://checkout.paycom.uz/" . $base64_params;

$ton_comment = base64_encode($order_id);
$ton_link    = "ton://transfer/" . $ton_wallet_address
. "?amount=" . round($ton_payment_amount * 1000000000)
. "&text=" . urlencode($ton_comment);

$text = "Assalomu alaykum, yaxshimisiz

Men quyidagi burtmani xarid qilmoqchiman! 👇🏻

️Turi: Telegram Stars
️Username: @$username_target
️Soni: $quantity ta
️Summa: " . number_format($amount) . " so'm
️ID: [$order_id]

To'lov qilishim uchun karta raqamingizni yubora olasizmi?";

$link = "https://t.me/unloced?text=" . urlencode($text);

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><tg-emoji emoji-id='5983399041197675256'>🏪</tg-emoji> Buyurtma yaratildi</b>

<blockquote>▪️<b>Turi:</b> <tg-emoji emoji-id='5942783678668085067'>⭐</tg-emoji>️ Telegram Stars
▪️<b>Username:</b> @$username_target
▪️<b>Soni:</b> $quantity ta
▪️<b>Summa:</b> " . number_format($amount) . " so'm
▪️<b>ID:</b> [$order_id]</blockquote>

<tg-emoji emoji-id='5350534149843203535'>💳</tg-emoji><a href=\"$link\"> Payme ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji><a href=\"$link\"> Tonkeeper ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5927169041595634481'>💳</tg-emoji> <b>To'lov amalga oshirilgach, buyurtma avtomatik yuboriladi</b>",
'parse_mode' => 'HTML',
'disable_web_page_preview'=>true,
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Payme", 'url' => $payme_url,'icon_custom_emoji_id' => "5350534149843203535"]],
[['text' => "Tonkeeper", 'url' => $ton_link, 'icon_custom_emoji_id' => "5406976471153545018"]]
]
])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}

// ====================== BUY PREMIUM ======================

if ((isset($data) && $data === "buy_premium") || $text == "/premium") {
$is_subscribed = check($from_id, $bot, false);
if (!$is_subscribed) {
majburiy($from_id, $bot, false);
exit();
}
$prices = [3 => $narx_premium_3,6 => $narx_premium_6,12 => $narx_premium_12];
$keyboard = [];
$row = [];
foreach ($prices as $month => $uzs_price) {
$btn_text = "$month oy - " . number_format($uzs_price) . " so‘m";
$row[] = ['text' => $btn_text,'callback_data' => "premium_{$month}_{$uzs_price}",'icon_custom_emoji_id' => "5388584703932522832"];
if (count($row) == 1) {
$keyboard[] = $row;
$row = [];
}
}
if (!empty($row)) {
$keyboard[] = $row;
}
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5388584703932522832'>✅</tg-emoji> <b>Telegram Premium</b>

<tg-emoji emoji-id='5879785854284599288'>ℹ️</tg-emoji> <b>IMKONIYATLAR</b>
<blockquote expandable><tg-emoji emoji-id='5877613700344450910'>⏲</tg-emoji> Tezroq yuklab olish tezligi
<tg-emoji emoji-id='5877316724830768997'>🗃</tg-emoji> Kengaytirilgan xotira limiti
<tg-emoji emoji-id='5771511103141975115'>📢</tg-emoji> Reklamalarsiz foydalanish
<tg-emoji emoji-id='5897554554894946515'>🎤</tg-emoji> Ovozni matnga aylantirish imkoniyati
<tg-emoji emoji-id='5942640218170461901'>🙂</tg-emoji> Animatsiyali emoji va eksklyuziv stikerlar
<tg-emoji emoji-id='5994297722574737553'>💬</tg-emoji> Kengaytirilgan limitlar va chat boshqaruvi</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Telegram Premium muddatini tanlang</b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}

if (isset($data) && strpos($data, "premium_") === 0) {
list(, $month, $uzs_price) = explode("_", $data);
if (empty($available)) {
$keyboard[] = [['text' => "Administrator", 'url' => "tg://user?id=".$admin, 'icon_custom_emoji_id' => "5920052658743283381"]];
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5879785854284599288'>ℹ️</tg-emoji> <b>Hozirda hech qanday muddatli Telegram Premium sotib olib bo‘lmaydi.</b>\n\n" .
"<tg-emoji emoji-id='5967591100532134862'>☎️</tg-emoji> Administratorga murojaat qilishingizni tavsiya qilamiz!",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
if (!in_array($month, $available)) {
$keyboard[] = [['text' => "Administrator", 'url' => "tg://user?id=".$admin, 'icon_custom_emoji_id' => "5920052658743283381"]];
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5879785854284599288'>ℹ️</tg-emoji> <b>Faqat " . implode(" va ", $available) . "-oylik Telegram Premium mavjud.</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
file_put_contents("step/$from_id.step", "username_premium");
file_put_contents("step/{$from_id}_month.txt", $month);
file_put_contents("step/{$from_id}_uzs_price.txt", $uzs_price);
$keyboardd[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchi nomi?</b>\n\n" .
"<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Telegram Premiumni yuborishdan oldin, qaysi profilga yuborilishini aniq belgilab olishimiz kerak.</i>\n" .
"<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>\n\n" .
"<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboardd])
]);
exit();
}

if (file_get_contents("step/$from_id.step") == "username_premium" && !empty($text)) {
$month = intval(@file_get_contents("step/{$from_id}_month.txt"));
$uzs_price = intval(@file_get_contents("step/{$from_id}_uzs_price.txt"));
$username_target = trim(str_replace("@", "", $text));
$username_input = trim($text);
if (strpos($username_input, "@") !== 0) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi @ bilan boshlanishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);    
exit();
}
if (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $username_target)) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi kamida 4 ta belgi bo'lishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$result = chekusername($username_target);
if (!$result['exists']) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <b>Qabul qiluvchi topilmadi!</b>\n\n" .
"<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning.</i>\n" .
"<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$resultPremium = chekPremiumUsername($username_target, 12);
if ($resultPremium['has_premium']) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5879785854284599288'>ℹ️</tg-emoji> <b>Bu foydalanuvchi allaqachon Telegram Premium obunasiga ega!</b>\n\n" .
"<blockquote>▪️ <b>Username:</b> @{$resultPremium['username']}\n" .
"▪️ Ushbu akkauntga Premium sovg‘a qilib bo‘lmaydi, chunki u allaqachon faol obunaga ega.</blockquote>\n\n" .
"<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Boshqa foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$turi = "premium";
// TON miqdorini hisoblash (webhook da tekshirish uchun saqlash kerak)
$ton_payment_amount = round($uzs_price / $final_price, 6);
$insert_order_sql = "INSERT INTO orders (amount, status, user_id, username, mountity, turi, ton_amount) VALUES (?, 'pending', ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $insert_order_sql);
mysqli_stmt_bind_param($stmt,"disisd",$uzs_price,$from_id,$username_target,$month,$turi,$ton_payment_amount);
mysqli_stmt_execute($stmt);
$new_id = mysqli_insert_id($connect);
$order_id = 'SORA-' . $new_id;
$update_sql = "UPDATE orders SET order_id = ? WHERE id = ?";
$update_stmt = mysqli_prepare($connect, $update_sql);
mysqli_stmt_bind_param($update_stmt, "si", $order_id, $new_id);
mysqli_stmt_execute($update_stmt);
$amount_tiyn = $uzs_price * 100;
$params_array = ["m={$payme_merchant_id}","ac.order_id={$order_id}","ac.user_id={$from_id}","a={$amount_tiyn}","l=uz","cr=UZS","ct=15000"];
$params_string = implode(';', $params_array);
$base64_params = base64_encode($params_string);
$payme_url = "https://checkout.paycom.uz/" . $base64_params;

$ton_comment = base64_encode($order_id);
$ton_link    = "ton://transfer/" . $ton_wallet_address
. "?amount=" . round($ton_payment_amount * 1000000000)
. "&text=" . urlencode($ton_comment);


$text = "Assalomu alaykum, yaxshimisiz

Men quyidagi burtmani xarid qilmoqchiman! 👇🏻

️Turi: Telegram Premium
️Username: @$username_target
Muddat: $month oy
️Summa: " . number_format($uzs_price) . " so'm
️ID: [$order_id]

To'lov qilishim uchun karta raqamingizni yubora olasizmi?";

$link = "https://t.me/unloced?text=" . urlencode($text);

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><tg-emoji emoji-id='5983399041197675256'>🏪</tg-emoji> Buyurtma yaratildi</b>

<blockquote>▪️<b>Turi:</b> <tg-emoji emoji-id='5942783678668085067'>⭐</tg-emoji>️ Telegram Premium
▪️<b>Username:</b> @$username_target
▪️<b>Muddat:</b> $month oy
▪️<b>Summa:</b> " . number_format($uzs_price) . " so'm
▪️<b>ID:</b> [$order_id]</blockquote>

<tg-emoji emoji-id='5350534149843203535'>💳</tg-emoji><a href=\"$link\"> Payme ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji><a href=\"$link\"> Tonkeeper ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5927169041595634481'>💳</tg-emoji> <b>To'lov amalga oshirilgach, buyurtma avtomatik yuboriladi</b>",
'parse_mode' => 'HTML',
'disable_web_page_preview'=>true,
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Payme", 'url' => $payme_url,'icon_custom_emoji_id' => "5350534149843203535"]],
[['text' => "Tonkeeper", 'url' => $ton_link, 'icon_custom_emoji_id' => "5406976471153545018"]]
]
])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}



// ====================== BUY GIFT ======================


if ((isset($data) && $data === "buy_gift") || (isset($data) && strpos($data, "gifts_page_") === 0) || $text == "/gift"){
$is_subscribed = check($from_id, $bot, false);
if (!$is_subscribed) {
majburiy($from_id, $bot, false);
exit();
}
$get = json_decode(file_get_contents("https://sora.sheralidev.uz/Gift_card/Giftlar.json"), true);
$all_gifts = $get['gifts'] ?? [];
usort($all_gifts, function($a, $b) {
return $a['stars'] <=> $b['stars'];
});
$per_page = 16;
$columns = 2;
if (strpos($data ?? '', "gifts_page_") === 0) {
$current_page = (int) str_replace("gifts_page_", "", $data);
} else {
$current_page = 0;
}

$total_gifts = count($all_gifts);
$total_pages = ceil($total_gifts / $per_page);

if ($current_page < 0) $current_page = 0;
if ($current_page >= $total_pages) $current_page = $total_pages - 1;

$offset = $current_page * $per_page;
$current_gifts = array_slice($all_gifts, $offset, $per_page);

$inline = [];
foreach ($current_gifts as $g) {
$emoji     = $g['sticker']['id'];
$emoj      = $g['sticker']['attributes'][1]['alt'];
$star_count = $g['stars'];
$gift_id   = $g['id'];

$price_per_1 = $narx / 50;
$uzs_price   = floor($price_per_1 * $star_count);

$inline[] = ['text' => "| " . number_format($uzs_price) . " so‘m",'callback_data' => "gift_{$id}_{$gift_id}_{$emoji}_{$emoj}_{$star_count}_{$uzs_price}",'icon_custom_emoji_id' => $emoji];
}

$inlinekeys = array_chunk($inline, $columns);

$nav_row = [];
if ($total_pages > 1) {
if ($current_page > 0) {
$nav_row[] = ['text' => ' ', 'callback_data' => "gifts_page_" . ($current_page - 1), 'icon_custom_emoji_id' => '5830060857530257978'];
} else {
$nav_row[] = ['text' => ' ', 'callback_data' => 'noop'];
}

$nav_row[] = ['text' => ($current_page + 1) . " / " . $total_pages, 'callback_data' => 'noop'];

if ($current_page < $total_pages - 1) {
$nav_row[] = ['text' => ' ', 'callback_data' => "gifts_page_" . ($current_page + 1), 'icon_custom_emoji_id' => '5886334622849044906'];
} else {
$nav_row[] = ['text' => ' ', 'callback_data' => 'noop'];
}
}

$nav_row2 = [['text' => 'Orqaga', 'callback_data' => 'back_to_main', 'icon_custom_emoji_id' => '6039539366177541657']];

if (!empty($nav_row)) {
$inlinekeys[] = $nav_row;
}
$inlinekeys[] = $nav_row2;

$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6032937473162614352'>🎁</tg-emoji> <b>Telegram Gift xarid qilish</b>\n\n" .
"<blockquote expandable><tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> " .
"Giftni yuborish jarayoni @SoraPay_Manager profili orqali anonim amalga oshiriladi</blockquote>\n\n" .
"<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Iltimos, yuborish uchun kerakli Giftni tanlang</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $inlinekeys])
]);

if (isset($callback_query_id)) {
$bot->answerCallbackQuery(['callback_query_id' => $callback_query_id]);
}
exit();
}


if (isset($data) && strpos($data, "gift_") === 0) {
list(, $id, $emoji, $gift_id, $emoj, $star_count, $uzs_price) = explode("_", $data);
if ($MyProfilStars < $star_count) {
$keyboard[] = [['text' => "Administrator", 'url' => "tg://user?id=".$admin, 'icon_custom_emoji_id' => "5920052658743283381"]];
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5879785854284599288'>ℹ️</tg-emoji> <b>Hozirda ushbu  <tg-emoji emoji-id=\"" . htmlspecialchars($gift_id) . "\">" . htmlspecialchars($emoj) . "</tg-emoji> Gift-ni sotib olib bo‘lmaydi.</b>\n\n" .
"<tg-emoji emoji-id='5967591100532134862'>☎️</tg-emoji> Administratorga murojaat qilishingizni tavsiya qilamiz!",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
file_put_contents("step/$from_id.step", "username_gift");
file_put_contents("step/{$from_id}_custom_emoji.txt", $emoji);
file_put_contents("step/{$from_id}_gift_id.txt", $gift_id);
file_put_contents("step/{$from_id}_emoji.txt", $emoj);
file_put_contents("step/{$from_id}_uzs_price.txt", $uzs_price);
$keyboardd[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchi nomi?</b>\n\n" .
"<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Biz Telegram Gift-ni qaysi profilga yuborishimizni aniqlashtirib olishimiz kerak.</i>\n\n" .
"<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>\n\n" .
"<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboardd])
]);
exit();
}

if (file_get_contents("step/$from_id.step") == "username_gift" && !empty($text)) {
$emoji = trim(@file_get_contents("step/{$from_id}_custom_emoji.txt"));
$gift_id = trim(@file_get_contents("step/{$from_id}_gift_id.txt"));
$emoj = trim(@file_get_contents("step/{$from_id}_emoji.txt"));
$uzs_price = intval(@file_get_contents("step/{$from_id}_uzs_price.txt"));
$username_target = trim(str_replace("@", "", $text));
$username_input = trim($text);
if (strpos($username_input, "@") !== 0) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi @ bilan boshlanishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);    
exit();
}
if (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $username_target)) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi kamida 4 ta belgi bo'lishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$result = chekusername($username_target);
if (!$result['exists']) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <b>Qabul qiluvchi topilmadi!</b>\n\n" .
"<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning.</i>\n\n" .
"<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}


$turi = "gift";
// TON miqdorini hisoblash (webhook da tekshirish uchun saqlash kerak)
$ton_payment_amount = round($uzs_price / $final_price, 6);
$insert_order_sql = "INSERT INTO orders (amount, status, user_id, username, gift, custom_emoji, gift_id, turi, ton_amount) VALUES (?, 'pending', ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $insert_order_sql);
mysqli_stmt_bind_param($stmt,"disssssd",$uzs_price, $from_id, $username_target, $emoj, $emoji, $gift_id, $turi, $ton_payment_amount);
mysqli_stmt_execute($stmt);
$new_id = mysqli_insert_id($connect);
$order_id = 'SORA-' . $new_id;
$update_sql = "UPDATE orders SET order_id = ? WHERE id = ?";
$update_stmt = mysqli_prepare($connect, $update_sql);
mysqli_stmt_bind_param($update_stmt, "si", $order_id, $new_id);
mysqli_stmt_execute($update_stmt);
$amount_tiyn = $uzs_price * 100;
$params_array = ["m={$payme_merchant_id}","ac.order_id={$order_id}","ac.user_id={$from_id}","a={$amount_tiyn}","l=uz","cr=UZS","ct=15000"];
$params_string = implode(';', $params_array);
$base64_params = base64_encode($params_string);
$payme_url = "https://checkout.paycom.uz/" . $base64_params;

$ton_comment = base64_encode($order_id);
$ton_link    = "ton://transfer/" . $ton_wallet_address
. "?amount=" . round($ton_payment_amount * 1000000000)
. "&text=" . urlencode($ton_comment);

$text = "Assalomu alaykum, yaxshimisiz

Men quyidagi burtmani xarid qilmoqchiman! 👇🏻

️Turi: Telegram Gift
️Username: @$username_target
Gift ID: $gift_id
️Summa: " . number_format($uzs_price) . " so'm
️ID: [$order_id]

To'lov qilishim uchun karta raqamingizni yubora olasizmi?";

$link = "https://t.me/unloced?text=" . urlencode($text);

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><tg-emoji emoji-id='5983399041197675256'>🏪</tg-emoji> Buyurtma yaratildi</b>

<blockquote>▪️<b>Turi:</b> <tg-emoji emoji-id='5942783678668085067'>⭐</tg-emoji>️ Telegram Gift
▪️<b>Username:</b> @$username_target
▪️<b>Gift:</b> <tg-emoji emoji-id=\"" . htmlspecialchars($gift_id) . "\">" . htmlspecialchars($emoj) . "</tg-emoji>
▪️<b>Summa:</b> " . number_format($uzs_price) . " so'm
▪️<b>ID:</b> [$order_id]</blockquote>

<tg-emoji emoji-id='5350534149843203535'>💳</tg-emoji><a href=\"$link\"> Payme ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji><a href=\"$link\"> Tonkeeper ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5927169041595634481'>💳</tg-emoji> <b>To'lov amalga oshirilgach, buyurtma avtomatik yuboriladi</b>",
'parse_mode' => 'HTML',
'disable_web_page_preview'=>true,
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Payme", 'url' => $payme_url,'icon_custom_emoji_id' => "5350534149843203535"]],
[['text' => "Tonkeeper", 'url' => $ton_link, 'icon_custom_emoji_id' => "5406976471153545018"]]
]
])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}


// ====================== BUY TON ======================

if ((isset($data) && $data === "buy_ton") || $text == "/ton") {
$is_subscribed = check($from_id, $bot, false);
if (!$is_subscribed) {
majburiy($from_id, $bot, false);
exit();
}
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='6028530359975548369'>💎</tg-emoji> <b>Yuklanmoqda</b>",
'parse_mode' => "HTML"
]);
usleep(50000);
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> <b>Ton coin</b>\n\n" .
"<blockquote expandable><b>Kurs: </b> <tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> 1 TON = <tg-emoji emoji-id='5406737344554374040'>☺️</tg-emoji> " . number_format($final_price) . " UZS</blockquote>\n\n" .
"<b><tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> Cheklovlar</b>\n" .
"<blockquote expandable>" .
"▫️<b>Minimal:</b> 0.1 ta\n" .
"▫️<b>Maksimal:</b> " . number_format($balance, 6) . " ta" .
"</blockquote>\n\n" .
"<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni raqam bilan yuboring</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
file_put_contents("step/$from_id.step", "ton_miqdor");
exit();
}


if (file_get_contents("step/$from_id.step") == 'ton_miqdor' && is_numeric(trim($text))) {
$quantity_ton = floatval(trim($text));   // ← floatval ishlatish SHART!
$quantity_ton = round($quantity_ton, 6);
if ($quantity_ton > $balance) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => "<blockquote expandable><b>Kurs: </b> <tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> 1 TON = <tg-emoji emoji-id='5406737344554374040'>☺️</tg-emoji> " . number_format($final_price) . " UZS</blockquote>\n
<tg-emoji emoji-id='5447644880824181073'>⚠️</tg-emoji> <b>Cheklovga amal qiling:</b>  

<blockquote>▫️<b> Minimal:</b> 0.1 ta
▫️<b> Maksimal:</b> " . number_format($balance, 6) . " ta</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni raqam bilan yuboring <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji></b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
if ($quantity_ton < 0.1) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<blockquote expandable><b>Kurs: </b> <tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> 1 TON = <tg-emoji emoji-id='5406737344554374040'>☺️</tg-emoji> " . number_format($final_price) . " UZS</blockquote>\n
<tg-emoji emoji-id='5447644880824181073'>⚠️</tg-emoji> <b>Cheklovga amal qiling:</b>  

<blockquote>▫️<b> Minimal:</b> 0.1 ta
▫️<b> Maksimal:</b> " . number_format($balance, 6) . " ta</blockquote>

<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Kerakli miqdorni raqam bilan yuboring <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji></b>",
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$uzs_summasi = floor($final_price * $quantity_ton);

file_put_contents("step/$from_id.step", "ton_hamyon");
file_put_contents("step/{$from_id}_uzs_summasi.txt", $uzs_summasi);
file_put_contents("step/{$from_id}_quantity_ton.txt", $quantity_ton);
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchini TON Hamyon Manzili?</b>

<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>TON yuborishdan oldin, qaysi manzilga yuborilishini aniq belgilab olishimiz kerak.</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>UQAHzWZlGLBAulX9koSL_2_C5_YO61AKahUsfl4kwvmhxJjk</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Qabul qiluvchi TON Hamyon Manzilini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}


if (file_get_contents("step/$from_id.step") == "ton_hamyon" && !empty($text)) {
$uzs_summasi = trim(@file_get_contents("step/{$from_id}_uzs_summasi.txt"));
$quantity_ton = trim(@file_get_contents("step/{$from_id}_quantity_ton.txt"));
$address = trim($text);
$api_url = "https://toncenter.com/api/v2/getAddressInformation?address=" . urlencode($address);
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);
$data = json_decode($response, true);
if (!$data || !isset($data['ok']) || $data['ok'] !== true) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <b>TON Hamyon Manzili haqiqiy emas!!</b>\n\n" .
"<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>TON Hamyon Manzili orqali hech qanday hamyonni topa olmadik. Iltimos mavjud TON Hamyon Manzilni yuboring.</i>\n\n" .
"<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>UQAHzWZlGLBAulX9koSL_2_C5_YO61AKahUsfl4kwvmhxJjk</code></blockquote>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$turi = "ton";
$insert_order_sql = "INSERT INTO orders (amount, status, user_id, quantity_ton, hamyon_ton, turi) VALUES (?, 'pending', ?, ?, ?, ?)";
$stmt = mysqli_prepare($connect, $insert_order_sql);
mysqli_stmt_bind_param($stmt,"disss",$uzs_summasi, $from_id, $quantity_ton, $address, $turi);
mysqli_stmt_execute($stmt);
$new_id = mysqli_insert_id($connect);
$order_id = 'SORA-' . $new_id;
$update_sql = "UPDATE orders SET order_id = ? WHERE id = ?";
$update_stmt = mysqli_prepare($connect, $update_sql);
mysqli_stmt_bind_param($update_stmt, "si", $order_id, $new_id);
mysqli_stmt_execute($update_stmt);
$amount_tiyn = $uzs_summasi * 100;
$params_array = ["m={$payme_merchant_id}","ac.order_id={$order_id}","ac.user_id={$from_id}","a={$amount_tiyn}","l=uz","cr=UZS","ct=15000"];
$params_string = implode(';', $params_array);
$base64_params = base64_encode($params_string);
$payme_url = "https://checkout.paycom.uz/" . $base64_params;

$text = "Assalomu alaykum, yaxshimisiz

Men quyidagi burtmani xarid qilmoqchiman! 👇🏻

️Turi: TON
TON Wallet: $address
Ton Miqdori: $quantity_ton
️Summa: " . number_format($uzs_summasi) . " so'm
️ID: [$order_id]

To'lov qilishim uchun karta raqamingizni yubora olasizmi?";

$link = "https://t.me/unloced?text=" . urlencode($text);

$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<b><tg-emoji emoji-id='5983399041197675256'>🏪</tg-emoji> Buyurtma yaratildi</b>

<blockquote>▪️<b>Turi <tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> TON</b>
▪️<b>TON Wallet:</b> <code>$address</code>
▪️<b>Ton Miqdori:</b> $quantity_ton ta
▪️<b>Summa:</b> " . number_format($uzs_summasi) . " so'm
▪️<b>ID:</b> [$order_id]</blockquote>

<tg-emoji emoji-id='5350534149843203535'>💳</tg-emoji><a href=\"$link\"> Payme ilovasi bo'lmaganda buyurtmalarga to'lovni amalga oshirish</a>

<tg-emoji emoji-id='5927169041595634481'>💳</tg-emoji> <b>To'lov amalga oshirilgach, buyurtma avtomatik yuboriladi</b>",
'parse_mode' => 'HTML',
'disable_web_page_preview'=>true,
'reply_markup' => json_encode([
'inline_keyboard' => [
[['text' => "Payme", 'url' => $payme_url,'icon_custom_emoji_id' => "5350534149843203535"]]
]
])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}


// ====================== STATISTIKA ======================

if (isset($data) && $data === "statistika") {
$sql_stars = "SELECT 
COUNT(*) as orders_count,
COALESCE(SUM(quantity), 0) as total_stars,
COALESCE(SUM(amount), 0) as total_uzs 
FROM orders 
WHERE turi = 'stars' AND status = 'paid' AND user_id = '$from_id'";

$sql_premium = "SELECT 
COUNT(CASE WHEN mountity = 3 THEN 1 END) as premium_3,
COUNT(CASE WHEN mountity = 6 THEN 1 END) as premium_6,
COUNT(CASE WHEN mountity = 12 THEN 1 END) as premium_12,
COALESCE(SUM(amount), 0) as total_uzs_premium
FROM orders 
WHERE turi = 'premium' AND status = 'paid' AND user_id = '$from_id'";

$sql_ton = "SELECT 
COUNT(*) as orders_count,
COALESCE(SUM(quantity_ton), 0) as total_ton,
COALESCE(SUM(amount), 0) as total_uzs_ton 
FROM orders 
WHERE turi = 'ton' AND status = 'paid' AND user_id = '$from_id'";

$sql_gift = "SELECT 
COUNT(*) as orders_count,
COALESCE(SUM(amount), 0) as total_uzs 
FROM orders 
WHERE turi = 'gift' AND status = 'paid' AND user_id = '$from_id'";
$stars   = mysqli_fetch_assoc(mysqli_query($connect, $sql_stars));
$premium = mysqli_fetch_assoc(mysqli_query($connect, $sql_premium));
$ton     = mysqli_fetch_assoc(mysqli_query($connect, $sql_ton));
$gift    = mysqli_fetch_assoc(mysqli_query($connect, $sql_gift));

$text = "<b><tg-emoji emoji-id='5931472654660800739'>📊</tg-emoji> Statistika</b>\n\n";

$text .= "<tg-emoji emoji-id='5363844691580177304'>💛</tg-emoji> <b>Telegram Stars</b>\n";
$text .= "<blockquote>";
$text .= "▫️ Buyurtmalar soni: <b>" . number_format($stars['orders_count']) . "</b> ta\n";
$text .= "▫️ Jami Stars: <b>" . number_format($stars['total_stars']) . "</b> ta\n";
$text .= "▫️ Kiritilgan pul: <b>" . number_format($stars['total_uzs']) . "</b> so‘m\n";
$text .= "</blockquote>\n";

$text .= "<tg-emoji emoji-id='5388584703932522832'>✅</tg-emoji> <b>Telegram Premium</b>\n";
$text .= "<blockquote>";
$text .= "▫️ 3 oylik: <b>" . number_format($premium['premium_3']) . "</b> ta\n";
$text .= "▫️ 6 oylik: <b>" . number_format($premium['premium_6']) . "</b> ta\n";
$text .= "▫️ 12 oylik: <b>" . number_format($premium['premium_12']) . "</b> ta\n";
$text .= "▫️ Jami kiritilgan pul: <b>" . number_format($premium['total_uzs_premium']) . "</b> so‘m\n";
$text .= "</blockquote>\n";

$text .= "<tg-emoji emoji-id='5406976471153545018'>☺️</tg-emoji> <b>TON Coin</b>\n";
$text .= "<blockquote>";
$text .= "▫️ Buyurtmalar soni: <b>" . number_format($ton['orders_count']) . "</b> ta\n";
$text .= "▫️ Jami TON: <b>" . number_format($ton['total_ton'], 6) . "</b> ta\n";
$text .= "▫️ Kiritilgan pul: <b>" . number_format($ton['total_uzs_ton']) . "</b> so‘m\n";
$text .= "</blockquote>\n";

$text .= "<tg-emoji emoji-id='6032937473162614352'>🎁</tg-emoji> <b>Telegram Gift</b>\n";
$text .= "<blockquote>";
$text .= "▫️ Buyurtmalar soni: <b>" . number_format($gift['orders_count']) . "</b> ta\n";
$text .= "▫️ Kiritilgan pul: <b>" . number_format($gift['total_uzs']) . "</b> so‘m\n";
$text .= "</blockquote>";

$keyboard = [[['text' => "Orqaga", 'callback_data' => "back_to_main", 'icon_custom_emoji_id' => "6039539366177541657"]]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text' => $text,
'parse_mode' => "HTML",
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}





if(isset($data) && mb_stripos($data,"ref") !== false){
$user_ref_id = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM users WHERE user_id = '$from_id'"))['user_ref_id'];
$user = mysqli_fetch_assoc(mysqli_query($connect,"SELECT balance, ref FROM users WHERE user_id='$from_id'"));
$ref = $user['ref'];
$bot->editMessageText([
'chat_id'=>$from_id,
'message_id'=>$message_id,
'text'=>"<tg-emoji emoji-id='5886440807325504167'>👥</tg-emoji> <b>Referal tizimi</b>

<tg-emoji emoji-id='6028435952299413210'>⁉️</tg-emoji> <b>U qanday ishlaydi?</b>

<tg-emoji emoji-id='5875180111744995604'>🎁</tg-emoji> <i>Botga do‘stlaringizni taklif qiling. Har bir taklif qilgan do'stingiz uchun +1 Stars olasiz:</i>

<blockquote>▫️<tg-emoji emoji-id='5960672896060756972'>⭐️</tg-emoji> Telegram Stars: +1 Stars</blockquote>

<tg-emoji emoji-id='5778145208411624388'>📊</tg-emoji> <b>Taklif qilgan do‘stlaringiz:</b> $ref ta

<tg-emoji emoji-id='6028530359975548369'>🔗</tg-emoji> <b>Referal havola:</b>

https://t.me/SoraPayBot?start=$user_ref_id

<tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji> <i>Havolani do‘stlaringizga yuboring va daromad oling!</i>
",
'parse_mode'=>"HTML",
'disable_web_page_preview'=>true,
'reply_markup'=>json_encode([
'inline_keyboard'=>[[['text'=>"Taklif qilish",'switch_inline_query'=>"Share",'icon_custom_emoji_id' => "5765071340847501478"]],
[['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]]
]
])
]);
exit();
}




$inline_query = $update->inline_query->query;
$inline_from_id = $update->inline_query->from->id;
$user = mysqli_fetch_assoc(mysqli_query($connect, "SELECT user_ref_id, balance, ref FROM users WHERE user_id='$inline_from_id'"));
$user_ref_id = $user['user_ref_id'];
$balance = $user['balance'];
$ref_count = $user['ref'];

if($inline_query !== null && $inline_query == "Share"){

$results[] = [
"type" => "article",
"id" => "1",
"title" => "Referal va holatingiz",
"description" => "💰 Balansingiz: $balance ta Stars\n📊 Taklif qilganlaringiz: $ref_count ta",
"thumb_url" => "https://sora.sheralidev.uz/logo/sora.jpg",
"input_message_content" => [
"message_text" => "🛍 <b>Telegram Premium yoki Stars kerakmi?</b>
Endi oson va tez!

👉🏻 <b><a href='https://t.me/SoraPayBot?start=$user_ref_id'>@SoraPayBot</a> orqali:</b>
<blockquote>• Telegram Premium sotib olasiz
• Telegram Stars xarid qilasiz
• 24/7 ishlaydi, admin kutish yo‘q
• To‘lovdan keyin 5 soniyada tushadi</blockquote>

<i>💰 Narxlar ochiq va qulay
🔐 Xavfsiz — profilingizga kirilmaydi!</i>

⭐️ <b><a href='https://t.me/SoraPayBot?start=$user_ref_id'>@SoraPayBot</a> Ishonchli • Tezkor • Hamyonbop</b>",
"parse_mode" => "HTML",
"disable_web_page_preview" => true
],
"reply_markup" => [
"inline_keyboard" => [
[
[
"text" => "💳 Sotib olish",
"url" => "https://t.me/SoraPayBot?start=$user_ref_id"
]
]
]
]
];

$bot->answerInlineQuery([
"inline_query_id" => $update->inline_query->id,
"cache_time" => 1,
"results" => json_encode($results),
]);

exit();
}






// ================= CAPTCHA CHECK =================
if(isset($data) && mb_stripos($data,"captcha_") !== false){
$value = explode("_",$data)[1];
$user = mysqli_fetch_assoc(mysqli_query($connect,"SELECT * FROM users WHERE user_id='$from_id'"));
if($user['captcha_required'] == 1 && $user['captcha_passed'] == 0){

if(time() - $user['captcha_time'] < 5){
mysqli_query($connect,"DELETE FROM users WHERE user_id='$from_id'");
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Shubhali faoliyat aniqlandi! O'tqazib yuborish /start</b>",
'parse_mode'=>'HTML'
]);
exit();
}

if($value == $user['captcha']){
mysqli_query($connect,"UPDATE users SET captcha_passed='1' WHERE user_id='$from_id'");
$ref_id = $user['ref_id'];

if($ref_id != NULL){
mysqli_query($connect,"UPDATE users SET balance = balance + 1 WHERE user_ref_id='$ref_id'");
mysqli_query($connect,"UPDATE users SET ref = ref + 1 WHERE user_ref_id='$ref_id'");
$ref_user_data = mysqli_fetch_assoc(mysqli_query($connect,"SELECT user_id, balance FROM users WHERE user_ref_id='$ref_id'"));
$bot->sendMessage([
'chat_id'=>$ref_user_data['user_id'],
'text'=>"<tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> <b>Siz taklif qilgan <a href='tg://user?id=$from_id'>$first_name</a> aktiv bo‘ldi!\n\nHisobingizga +1 Stars qo‘shildi\n\n<tg-emoji emoji-id='5960672896060756972'>⭐️</tg-emoji> Jami balans: ".$ref_user_data['balance']."</b>",
'parse_mode'=>'HTML'
]);
}
$bot->editMessageText([
'chat_id'=>$from_id,
'message_id'=>$message_id,
'text'=>"<tg-emoji emoji-id='5994750571041525522'>👋</tg-emoji> <i><b><u>Assalom aleykum $first_name Misolni to'g'ri yechdingiz, botga xush kelibsiz!</u></b></i>

<tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Bot orqali quyidagilarni xarid qilish mumkin.</i>

<b>Boshlash uchun xizmatni tanlang:</b> <tg-emoji emoji-id='5470112026548257635'>⬇️</tg-emoji>",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode(['inline_keyboard'=>$menu])
]);
exit();
}else{
mysqli_query($connect,"UPDATE users SET captcha_try=captcha_try+1 WHERE user_id='$from_id'");
$user['captcha_try']++;

if($user['captcha_try'] >= 3){
mysqli_query($connect,"DELETE FROM users WHERE user_id='$from_id'");
$bot->editMessageText([
'chat_id'=>$from_id,
'message_id'=>$message_id,
'text'=>"<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Juda ko‘p xato urinish! Qayta /start bosing</b>",
'parse_mode'=>'HTML'
]);
exit();
}

$a = rand(1,9);
$b = rand(1,9);
$new_captcha = $a + $b;

$fake1 = $new_captcha + rand(1,3);
$fake2 = max(1, $new_captcha - rand(1,3));

$options = [$new_captcha, $fake1, $fake2];
shuffle($options);

mysqli_query($connect,"UPDATE users SET 
captcha='$new_captcha',
captcha_time='".time()."'
WHERE user_id='$from_id'");

$keyboard = [
'inline_keyboard'=>[
[
['text'=>$options[0],'callback_data'=>"captcha_".$options[0]],
['text'=>$options[1],'callback_data'=>"captcha_".$options[1]],
['text'=>$options[2],'callback_data'=>"captcha_".$options[2]],
]
]
];

$bot->editMessageText([
'chat_id'=>$from_id,
'message_id'=>$message_id,
'text'=>"<tg-emoji emoji-id='5881702736843511327'>⚠️</tg-emoji> <b>Noto‘g‘ri!</b>\n\n<tg-emoji emoji-id='5258113901106580375'>⌛️</tg-emoji> <b>Yangi misolni yeching:</b>\n\n$a + $b = ?",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode($keyboard)
]);
}
}
exit();
}


if (isset($data) && strpos($data, "profil") === 0) {
$user = mysqli_fetch_assoc(mysqli_query($connect,
"SELECT balance, ref FROM users WHERE user_id='$from_id'"));
$balance = $user['balance'];
$ref = $user['ref'];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text'=>"<tg-emoji emoji-id='5931409969613116639'>🛡</tg-emoji> <b>Sizning profilingiz va holatlar!</b>

<blockquote>▫️<b> Sizda mavjud stars:</b> $balance ta
▫️<b> Referallaringiz soni:</b> $ref ta
▫️<b> Stars chiqarish minimal:</b> 50 ta</blockquote>",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[['text'=>"Stars yechish",'callback_data'=>"withdraw_stars",'icon_custom_emoji_id' => "5875180111744995604"]],
[['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]]
]
])
]);
exit();
}

if (isset($data) && strpos($data, "withdraw_stars") === 0) {
$user = mysqli_fetch_assoc(mysqli_query($connect,
"SELECT balance FROM users WHERE user_id='$from_id'"));
if($user['balance'] < 50){
$bot->answerCallbackQuery([
'callback_query_id'=>$update->callback_query->id,
'text'=>"Minimal 50 stars kerak!",
'show_alert'=>true
]);
exit();
}
file_put_contents("step/$from_id.step","amount");
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->editMessageText([
'chat_id' => $from_id,
'message_id' => $message_id,
'text'=>"<tg-emoji emoji-id='6008258140108231117'>🔣</tg-emoji> <b>Miqdorni raqam bilan yuboring</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}

if(isset($text) && file_exists("step/$from_id.step") && file_get_contents("step/$from_id.step") == "amount"){
$user = mysqli_fetch_assoc(mysqli_query($connect,
"SELECT balance FROM users WHERE user_id='$from_id'"));
$balance = $user['balance'];
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
if(!is_numeric($text) || $text < 50 || $text > $balance){
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5447644880824181073'>⚠️</tg-emoji> <b>Cheklovga amal qiling:</b>

<blockquote>▫️<b>Minimal:</b> 50 ta  
▫️<b>Maksimal:</b> $balance ta (sizning balansingiz)
</blockquote>",
'parse_mode'=>'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
file_put_contents("step/$from_id.amount",$text);
file_put_contents("step/$from_id.step","username");
$bot->sendMessage([
'chat_id'=>$from_id,
'text' => "<tg-emoji emoji-id='5954175920506933873'>👤</tg-emoji> <b>Qabul qiluvchi nomi?</b>

<blockquote><tg-emoji emoji-id='5793933761594789855'>💬</tg-emoji> <i>Telegram Starslarni yuborishdan oldin, qaysi profilga yuborilishini aniq belgilab olishimiz kerak.</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}

if(isset($text) && file_exists("step/$from_id.step") && file_get_contents("step/$from_id.step") == "username"){
$username_target = trim(str_replace("@", "", $text));
$username_input = trim($text);
if (strpos($username_input, "@") !== 0) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi @ bilan boshlanishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);    
exit();
}

if (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $username_target)) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Foydalanuvchi nomi kamida 4 ta belgi bo'lishi kerak!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$result = chekusername($username_target);
if (!$result['exists']) {
$keyboard[] = [['text' => "Orqaga", 'callback_data' => "back_to_main",'icon_custom_emoji_id' => "6039539366177541657"]];
$bot->sendMessage([
'chat_id' => $from_id,
'text' => "<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <i><b>Qabul qiluvchi topilmadi!</b></i>

<blockquote><tg-emoji emoji-id='5886440807325504167'>💬</tg-emoji> <i>Ushbu foydalanuvchi nomi orqali hech qanday profilni topa olmadik. Iltimos mavjud foydalanuvchi nomidan foydalaning</i>

<tg-emoji emoji-id='5778145208411624388'>👤</tg-emoji> Namuna: <code>@SoraPay_Manager</code></blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ</tg-emoji> <b>Foydalanuvchi nomini yuboring:</b>",
'parse_mode' => 'HTML',
'reply_markup' => json_encode(['inline_keyboard' => $keyboard])
]);
exit();
}
$amount = file_get_contents("step/$from_id.amount");
file_put_contents("step/$from_id.step","confirm");
file_put_contents("step/$from_id.username",$username_input);
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5994750571041525522'>❗️</tg-emoji> <b>Tasdiqlang:</b>

<blockquote>▫️<tg-emoji emoji-id='5960672896060756972'>⭐️</tg-emoji> <b>Miqdor:</b> $amount ta Stars  
▫️<tg-emoji emoji-id='5775887550262546277'>👤</tg-emoji> <b>Username:</b> $username_input</blockquote>

<tg-emoji emoji-id='6028435952299413210'>ℹ️</tg-emoji> <b>Ushbu amalni tasdiqlaysizmi?</b>",
'parse_mode'=>'HTML',
'reply_markup'=>json_encode([
'inline_keyboard'=>[
[
['text'=>"Ha",'callback_data'=>"confirm_yes",'icon_custom_emoji_id' => "5992199545151295755"],
['text'=>"Yo‘q",'callback_data' => "back_to_main",'icon_custom_emoji_id' => "5879896690210639947"]
]
]
])
]);
exit();
}


if (isset($data) && strpos($data, "confirm_yes") === 0) {
$amount = file_get_contents("step/$from_id.amount");
$username = file_get_contents("step/$from_id.username");

$user = mysqli_fetch_assoc(mysqli_query($connect,
"SELECT balance FROM users WHERE user_id='$from_id'"));

if($user['balance'] < $amount){
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='6028435952299413210'>ℹ️</tg-emoji> <b>Balans yetarli emas!</b>",
'parse_mode'=>'HTML',
]);
exit();
}

$api_url = "https://sora.sheralidev.uz/BuyStars/main.php?" . http_build_query([
'quantity' => $amount,
'username' => $username
]);

$response = @file_get_contents($api_url);
$result = json_decode($response, true);

if($result && isset($result['status']) && $result['status'] === true){
mysqli_query($connect,"UPDATE users SET balance = balance - $amount WHERE user_id='$from_id'");
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"<tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> <b>Referal orqali ishlagan starsingiz yuborildi!</b>

<blockquote>
▪️<b>Turi:</b> Telegram Stars  
▪️<b>Username:</b> $username  
▪️<b>Soni:</b> $amount ta  
</blockquote>",
'parse_mode'=>'HTML'
]);
}else{
$bot->sendMessage([
'chat_id'=>$from_id,
'text'=>"❌ API xatolik: Stars yuborilmadi"
]);
}
@unlink("step/$from_id.step");
@unlink("step/{$from_id}_quantity.txt");
@unlink("step/{$from_id}_month.txt");
@unlink("step/{$from_id}_custom_emoji.txt");
@unlink("step/{$from_id}_emoji.txt");
@unlink("step/{$from_id}_gift_id.txt");
@unlink("step/{$from_id}_uzs_price.txt");
@unlink("step/{$from_id}_uzs_summasi.txt");
@unlink("step/{$from_id}_quantity_ton.txt");
@unlink("step/$from_id.amount");
@unlink("step/$from_id.username");
exit();
}



if (isset($update->business_message)) {
$biznes = $update->business_message;
$chat_id = $biznes->chat->id ?? null;
$text = trim($biznes->text ?? '');
$business_connection_id = $biznes->business_connection_id ?? null;
if (!$chat_id || !$business_connection_id) {
exit();
}

$trigger = "To'lov qilishim uchun karta raqamingizni yubora olasizmi?";
if (str_contains($text, $trigger)) {
$bot->sendMessage([
'chat_id' => $chat_id,
'text' => "<tg-emoji emoji-id='5409078930659357770'>💸</tg-emoji> <code>9860 6004 1277 2029</code>

<tg-emoji emoji-id='5409078930659357770'>💸</tg-emoji> <b>MUSURMONOV BEKZOD</b>

<tg-emoji emoji-id='5408943604829794451'>⚠️</tg-emoji> To'lovdan so'ng chek yuboring!",
'parse_mode'=>'HTML',
'business_connection_id' => $business_connection_id
]);
exit();
}
}










include("admin/admin.php");