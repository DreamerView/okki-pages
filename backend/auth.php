<?php 

    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    if (!isset($_COOKIE['0auth'])) {
        exit(json_encode(["auth" => 0]));
    }

    require_once "_okki.php";
    $okki = new Okki();
    $auth = $okki->aes256EncryptDecrypt($_COOKIE['0auth'], false);

    if (!$auth) {
        exit(json_encode(["auth" => 0]));
    }

    $auth = json_decode($auth, true);

    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    $get = $db->fetch("SELECT 
        u.`email`,
        AES_DECRYPT(u.`fullname`, ?) AS `fullname`,
        u.`password`,
        u.`login`,
        IF(EXISTS(SELECT 1 FROM `notification` n WHERE n.`to` = u.`uuid` AND n.`read` = 0), 1, 0) AS `has_unread_notifications`
    FROM `users` u
    WHERE u.`uuid` = ?
    ", [GLOBAL_ENCRYPT_KEY,$auth['uuid']]);

    if (!$get) {
        exit(json_encode(["auth" => 0]));
    }

    $fullname = $get['fullname'];
    exit(json_encode(
            [
                "auth" => 1, 
                "fullname" => $fullname, 
                "uuid" => $auth['uuid'],
                "email" => $get['email'],
                "login" =>  $get["login"],
                "notification" => $get["has_unread_notifications"]
        ]));
?>