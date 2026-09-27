<?php
$FRAGMENT_HASH = 'fe968d72c8594bdbaa';
$stel_ssid = 'cb70b75aca9ae5ad92_120072001367329106';
$stel_dt = '-180';
$stel_ton_token= 'w4bVVl81WPv9_eukHri3TLiqA7BD0vjdQ8Lbrm1Qz65T86qaXh3qlqrSk9b3kcG0A4bojPPgTjb8agqSgpHZguqZlRut2_enZj0QME_7hZKHyloB7yEXk_n4OT8JGycMrKypFO1GkfQVqCdv-y-XAsjVHiKUsItq5Uk8AgQjiaySH0WVyp-wF2BfxMlGehHSGLAFcD0s';
$stel_token = '85b4c6ec4a46b1f3ef0c13ea29f0596d85b4c6f685b4cc1d4706db261365fe62014c9';

function getFragmentCookies() {
    global $stel_ssid, $stel_dt, $stel_ton_token, $stel_token;
    return [
        'stel_ssid' => $stel_ssid,
        'stel_dt' => $stel_dt,
        'stel_ton_token' => $stel_ton_token,
        'stel_token' => $stel_token,
    ];
}

/**
 * Username mavjudligini tekshirish (Stars uchun)
 */
function chekusername($username) {
    global $FRAGMENT_HASH;

    $url = "https://fragment.com/api?hash=" . $FRAGMENT_HASH;
    $postData = [
        'query'  => $username,
        'method' => 'searchStarsRecipient'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_COOKIE, http_build_query(getFragmentCookies(), '', '; '));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json, text/javascript, */*; q=0.01",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "Origin: https://fragment.com",
        "Referer: https://fragment.com/stars/buy",
        "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1",
        "X-Requested-With: XMLHttpRequest"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "HTTP Error: " . $httpCode];
    }

    $json = json_decode($response, true);

    if (isset($json['found']) && is_array($json['found'])) {
        $found = $json['found'];
        if (isset($found['recipient']) && !empty($found['recipient'])) {
            return [
                'exists'    => true,
                'username'  => $username,
                'recipient' => $found['recipient'],
                'name'      => $found['name'] ?? $username,
                'avatar'    => $found['avatar'] ?? null
            ];
        }
    }

    return [
        'exists'  => false,
        'username'=> $username,
        'message' => 'Username topilmadi yoki noto‘g‘ri'
    ];
}

/**
 * Yangi funksiya: Premium sovg‘a qilish mumkinligini tekshirish
 * (agar foydalanuvchida allaqachon Premium obunasi bo‘lsa, xatolik qaytaradi)
 */
function chekPremiumUsername($username, $months = 12) {
    global $FRAGMENT_HASH;

    $url = "https://fragment.com/api?hash=" . $FRAGMENT_HASH;

    $postData = [
        'query'   => $username,     // @siz ham yuborish mumkin, lekin odatda sizsiz
        'months'  => (int)$months,  // 3, 6 yoki 12
        'method'  => 'searchPremiumGiftRecipient'
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_COOKIE, http_build_query(getFragmentCookies(), '', '; '));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/json, text/javascript, */*; q=0.01",
        "Content-Type: application/x-www-form-urlencoded; charset=UTF-8",
        "Origin: https://fragment.com",
        "Referer: https://fragment.com/premium/gift",   // muhim: premium sahifasiga mos
        "User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1",
        "X-Requested-With: XMLHttpRequest"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return ['error' => "HTTP Error: " . $httpCode];
    }

    $json = json_decode($response, true);

    // 1. Agar "This account is already subscribed to Telegram Premium." xatosi kelsa
    if (isset($json['error']) && str_contains($json['error'], 'already subscribed')) {
        return [
            'exists'      => true,
            'has_premium' => true,
            'username'    => $username,
            'message'     => 'Bu akkaunt allaqachon Telegram Premium obunasiga ega.'
        ];
    }

    // 2. Muvaffaqiyatli topildi (sovg‘a qilish mumkin)
    if (isset($json['found']) && is_array($json['found'])) {
        $found = $json['found'];
        if (isset($found['recipient']) && !empty($found['recipient'])) {
            return [
                'exists'      => true,
                'has_premium' => false,
                'username'    => $username,
                'recipient'   => $found['recipient'],
                'name'        => $found['name'] ?? $username,
                'avatar'      => $found['avatar'] ?? $found['photo'] ?? null, // ba'zida photo keladi
                'message'     => 'Premium sovg‘a qilish mumkin.'
            ];
        }
    }

    // 3. Boshqa holatlar (topilmadi, xato va h.k.)
    return [
        'exists'      => false,
        'has_premium' => false,
        'username'    => $username,
        'message'     => $json['error'] ?? 'Username topilmadi yoki Premium sovg‘a qilish imkoni yo‘q.'
    ];
}
