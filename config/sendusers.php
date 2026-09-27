<?php

$connect = mysqli_connect('localhost', 'stars_user', "StarsBot_2026!", 'stars_bot');

$jonatish = 90;
$tokenbot = "8928348194:AAE1bvqdRMj43ZRVPjbsMzSk8VU7bWk9uxk";
$admin = "2142292702";

date_default_timezone_set("Asia/Tashkent");
$soat = date('H:i');

$result = mysqli_query($connect, "SELECT * FROM `sendusers` WHERE `status` = 'active' LIMIT 1");
if (mysqli_num_rows($result) == 0) exit();

$row = mysqli_fetch_assoc($result);
$jv      = $row['joriy_vaqt'];
$mesid   = $row['mid'];
$limit   = $row['soni'];
$send    = $row['send'];
$nosend  = $row['nosend'];
$xturi   = $row['holat'];
$tugma   = $row['button'];
$row_id  = $row['id'];

$reply_markup = ($tugma == "bnVsbA==" || empty($tugma)) ? "" : base64_decode($tugma);

if ($soat == $jv) {

$res = mysqli_query($connect, "SELECT * FROM `users` LIMIT $limit, $jonatish");

while ($rowsend = mysqli_fetch_assoc($res)) {
$id = $rowsend['user_id'];
usleep(100000);

$url = "https://api.telegram.org/bot$tokenbot/$xturi"
. "?chat_id=$id"
. "&from_chat_id=$admin"
. "&message_id=$mesid"
. "&parse_mode=HTML"
. "&reply_markup=" . urlencode($reply_markup);

$okk = file_get_contents($url);
$ok  = json_decode($okk, true)['ok'];

if ($ok) {
$send++;
mysqli_query($connect, "UPDATE `sendusers` SET `send` = '$send' WHERE `id` = '$row_id'");
} else {
$nosend++;
mysqli_query($connect, "UPDATE `sendusers` SET `nosend` = '$nosend' WHERE `id` = '$row_id'");
}
}

$limit += $jonatish;
$vt = date('H:i', strtotime("+1 minutes"));

mysqli_query($connect, "UPDATE `sendusers` SET `joriy_vaqt` = '$vt', `soni` = '$limit' WHERE `id` = '$row_id'");

$rest = mysqli_query($connect, "SELECT * FROM `users` LIMIT $limit, 1");

if (mysqli_num_rows($rest) == 0) {

mysqli_query($connect, "UPDATE `sendusers` SET `status` = 'done' WHERE `id` = '$row_id'");

$jami = $send + $nosend;

$txad = urlencode(
"<tg-emoji emoji-id='5332375302993624259'>😄</tg-emoji> <u>Xabar yuborish yakunlandi!</u>\n\n"
. "<blockquote expandable>"
. "<tg-emoji emoji-id='5954224165874569584'>💬</tg-emoji> Yuborildi: <b>$send</b> ta\n"
. "<tg-emoji emoji-id='5886496611835581345'>👤</tg-emoji> Yuborilmadi: <b>$nosend</b> ta\n"
. "<tg-emoji emoji-id='5931472654660800739'>📊</tg-emoji> Jami: <b>$jami</b> ta"
. "</blockquote>"
);
file_get_contents("https://api.telegram.org/bot$tokenbot/sendMessage?chat_id=$admin&text=$txad&parse_mode=HTML");
}
}