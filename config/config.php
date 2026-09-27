<?php
define('BOT_TOKEN', '8928348194:AAE1bvqdRMj43ZRVPjbsMzSk8VU7bWk9uxk');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
define('DB_HOST', 'localhost');
define('DB_USER', 'stars_user');
define('DB_PASS', 'StarsBot_2026!');
define('DB_NAME', 'stars_bot');
define('DEBUG', false);

global $connect;
$connect = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connect->connect_error) {
die('Database Connection Error: ' . $connect->connect_error);
}

$connect->set_charset("utf8mb4");

if (DEBUG) {
error_reporting(E_ALL);
ini_set('display_errors', 1);
} else {
error_reporting(0);
ini_set('display_errors', 0);
}
?>
