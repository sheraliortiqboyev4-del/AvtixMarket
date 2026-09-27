<?php

$api_key = "AH6VGUQHYLVXFGQAAAAE6SCSDCDSXQAYSSPI4ZLUERNDXWDVI";
$wallet_address = "UQDBYjC-qIKn38OMJXNN4ZZd73H-EZhxjBMOMtq-ABE1LD2v"; // EQ... yoki UQ... manzil

$url = "https://tonapi.io/v2/accounts/" . urlencode($wallet_address);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);

if ($http_code === 200 && isset($data['balance'])) {
    $balance_nano = $data['balance'];
    $balance_ton = $balance_nano / 1e9;
    $text .= "" . number_format($balance_ton, 4) . "";

    file_put_contents('balance.txt', $text);
    echo "Balans saqlandi: $balance_ton TON";
} else {
    $error = $data['error'] ?? $response;
    echo "Xato ($http_code): $error";
}