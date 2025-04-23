<?php 

    header("Content-Type: application/json");
    
    $inputData = file_get_contents('php://input');

    // Декодирование JSON в ассоциативный массив
    $data = json_decode($inputData, true);

    if(isset($data['email'])&&isset($data['password'])) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        require_once "_okki.php";
        $okki = new Okki();
        $db->start();
        try {
            $email = htmlspecialchars($data['email']);
            $checkEmailExist = $db->fetch("SELECT `password`,`uuid` FROM `users` WHERE `email`=?",[$email]);
            if(!$checkEmailExist) {
                exit(json_encode(['status'=>"error","error"=>"email-not-exist"]));
            }
            if(password_verify($data['password'],$checkEmailExist['password'])) {
                $auth = json_encode(["uuid"=>$checkEmailExist['uuid'],"time"=>time()]);
                $enctryptedAuth = $okki->aes256EncryptDecrypt($auth,true);
                setcookie(
                    "0auth", // Имя cookie
                    $enctryptedAuth, // Значение cookie
                    [
                        'expires' => time() + 3600, // Время жизни cookie (1 час)
                        'path' => '/', // Путь, на котором cookie доступна
                        'domain' => '', // Домен, для которого доступна cookie (оставьте пустым, если для текущего)
                        'secure' => true, // Устанавливаем cookie только для HTTPS
                        'httponly' => true, // Запрещаем доступ к cookie через JavaScript
                        'samesite' => 'Strict' // Политика SameSite (например, Strict или Lax)
                    ]
                );
                exit(json_encode(["status"=>"success"]));
            } else {
                exit(json_encode(["status"=>"error","error"=>"password-error"]));
            }            
            $db->finish();
        } catch(error) {
            exit(json_encode(["status"=>"error","error"=>"database-error"]));
        }
    } else {
        exit(json_encode(["status"=>"error","error"=>"missing"]));
    }
?>