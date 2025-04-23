<?php 

    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    $authFlag = false;
    $user_auth_uuid = null;
    if (!isset($_COOKIE['0auth'])) return;

    require_once "_okki.php";
    $okki = new Okki();
    $auth = $okki->aes256EncryptDecrypt($_COOKIE['0auth'], false);

    if (!$auth) return;

    $auth = json_decode($auth, true);

    function checkAuthFrontend($param) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        $get = $db->fetch("SELECT `uuid`,`login` FROM `users` WHERE `uuid` = ?", [$param]);
        $db->close();
        return $get;
    }

    $user_auth_params = checkAuthFrontend($auth['uuid']);

    $user_auth_uuid = $user_auth_params['uuid'];
    $user_auth_login = $user_auth_params['login'];

    $authFlag = isset($user_auth_uuid)?true:false;
?>