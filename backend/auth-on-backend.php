<?php 
include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";

// Проверка куки
if (!isset($_COOKIE['0auth'])) {
    header("Location: /");
    exit;
}

require_once "_okki.php";
$okki = new Okki();
$auth = $okki->aes256EncryptDecrypt($_COOKIE['0auth'], false);

// Проверка дешифровки
if (!$auth) {
    header("Location: /");
    exit;
}

// Декодирование JSON
$auth = json_decode($auth, true);
if (!is_array($auth) || !isset($auth['uuid'])) {
    header("Location: /");
    exit;
}

// Работа с БД
require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
try {
    $db = new SimpleDB();
    $get = $db->fetch("SELECT `uuid` FROM `users` WHERE `uuid` = ?", [$auth['uuid']]);

    if (!$get) {
        header("Location: /");
        exit;
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: /");
    exit;
}

// Если дошли сюда, пользователь авторизован
?>