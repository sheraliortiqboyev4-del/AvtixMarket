<?php
header("Content-Type: application/json");

define('PRICE_FILE', 'price_ton_uzs.txt');   // Fayl nomi

// Asosiy funksiya
function getTonPriceInUZS() {
    $result = [
        "success" => false,
        "ton_price_usd" => 0,
        "ton_price_uzs" => 0,
        "ton_price_formatted" => "0 so‘m",
        "cached" => false,
        "timestamp" => date("Y-m-d H:i:s"),
        "error" => ""
    ];

    // 1. Yangi kursni olishga harakat qilamiz
    $fresh_price = fetchFreshTonPrice();

    if ($fresh_price['success']) {
        // Yangi narxni faylga saqlaymiz
        savePriceToFile($fresh_price['ton_price_uzs']);

        $result["success"] = true;
        $result["ton_price_usd"] = $fresh_price['ton_price_usd'];
        $result["ton_price_uzs"] = $fresh_price['ton_price_uzs'];
        $result["ton_price_formatted"] = number_format($fresh_price['ton_price_uzs'], 0, '.', ' ') . " so‘m";
        $result["cached"] = false;
        $result["source"] = "Live API";
    } 
    else {
        // API ishlamasa — fayldan eski narxni olamiz
        $cached_price = getCachedPrice();
        
        if ($cached_price > 0) {
            $result["success"] = true;
            $result["ton_price_uzs"] = $cached_price;
            $result["ton_price_formatted"] = number_format($cached_price, 0, '.', ' ') . " so‘m";
            $result["cached"] = true;
            $result["source"] = "Cached from file";
            $result["error"] = $fresh_price['error'] ?? "API ishlamadi, cache ishlatildi";
        } else {
            $result["error"] = "API ishlamadi va cache faylida ham narx yo‘q";
        }
    }

    return $result;
}

// Yangi kursni olish (CoinGecko + ExchangeRate)
function fetchFreshTonPrice() {
    $tonUrl = "https://api.coingecko.com/api/v3/simple/price?ids=the-open-network&vs_currencies=usd";
    
    $tonData = fetchJson($tonUrl);
    $tonUsd = $tonData['the-open-network']['usd'] ?? 0;

    if ($tonUsd <= 0) {
        return ["success" => false, "error" => "CoinGecko API xatosi"];
    }

    // USD → UZS (bir nechta manba)
    $uzsRate = 0;
    $sources = [
        "https://api.exchangerate.host/latest?base=USD",
        "https://api.exchangerate-api.com/v4/latest/USD"
    ];

    foreach ($sources as $url) {
        $data = fetchJson($url);
        $uzsRate = $data['rates']['UZS'] ?? 0;
        if ($uzsRate > 0) break;
    }

    if ($uzsRate <= 0) {
        return ["success" => false, "error" => "USD→UZS kursini olishda xato"];
    }

    $tonUzs = round($tonUsd * $uzsRate);

    return [
        "success" => true,
        "ton_price_usd" => round($tonUsd, 4),
        "ton_price_uzs" => $tonUzs
    ];
}

// Yordamchi: JSON olish
function fetchJson($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
    $response = curl_exec($ch);
    curl_close($ch);

    return $response ? json_decode($response, true) : [];
}

// Faylga saqlash
function savePriceToFile($price) {
    file_put_contents(PRICE_FILE, $price, LOCK_EX);
}

// Fayldan o'qish
function getCachedPrice() {
    if (file_exists(PRICE_FILE)) {
        $price = trim(file_get_contents(PRICE_FILE));
        return is_numeric($price) ? (int)$price : 0;
    }
    return 0;
}

// ================== Natijani chiqarish ==================
$result = getTonPriceInUZS();
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>