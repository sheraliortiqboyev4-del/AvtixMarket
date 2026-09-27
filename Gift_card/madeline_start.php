<?php
// MadelineProto session yaratish uchun
require_once 'madeline.php';

try {
    echo "MadelineProto session yaratilmoqda...\n";
    
    $MadelineProto = new \danog\MadelineProto\API('session.madeline');
    
    echo "Telegram login qiling...\n";
    $MadelineProto->start();
    
    echo "Session muvaffaqiyatli yaratildi!\n";
    echo "Endi userbot.php ishlay oladi.\n";
    
    // Test qilish
    $me = $MadelineProto->getSelf();
    echo "Login qilingan akkaunt: " . $me['first_name'] . " " . ($me['last_name'] ?? '') . "\n";
    echo "Username: @" . ($me['username'] ?? 'mavjud emas') . "\n";
    echo "ID: " . $me['id'] . "\n";
    
} catch (Exception $e) {
    echo "Xatolik: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>
