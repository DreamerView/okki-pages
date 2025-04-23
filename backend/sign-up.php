<?php 

    header("Content-Type: application/json");

    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    $inputData = file_get_contents('php://input');

    // Декодирование JSON в ассоциативный массив
    $data = json_decode($inputData, true);

    if(isset($data['email']) && isset($data['fullname']) && isset($data['password'])) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        require_once "_okki.php";
        $okki = new Okki();
        
        $db->start();
        try {
            $checkEmailExist = $db->fetch("SELECT `email`,`uuid` FROM `users` WHERE `email`=?", [$data['email']]);
            if ($checkEmailExist) {
                header('Content-Type: application/json');
                exit(json_encode(['status' => "error", "error" => "email-exist"]));
            }
            $uuid = uniqid();
            $password = password_hash($data['password'], PASSWORD_DEFAULT);
            $fullname = $data['fullname'];

            $db->execute("INSERT INTO `users` (`uuid`, `login`, `email`, `fullname`, `password`) 
                VALUES (?, ?, ?, AES_ENCRYPT(?, ?) , ?);", 
                [$uuid, $uuid, $data['email'], $fullname, GLOBAL_ENCRYPT_KEY ,$password]);

            $db->finish();

            $auth = json_encode(["uuid" => $uuid, "time" => time()]);
            $encryptedAuth = $okki->aes256EncryptDecrypt($auth, true);
            
            setcookie(
                "0auth", 
                $encryptedAuth, 
                [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]
            );

            header('Content-Type: application/json');
            exit(json_encode(["status" => "success"]));
        } catch (Exception $e) { 
            $db->stop();
            header('Content-Type: application/json');
            exit(json_encode(["status" => "error", "error" => $e->getMessage()]));
        }
    } else {
        header('Content-Type: application/json');
        exit(json_encode(["status" => "error", "error" => "missing"]));
    }
?>
