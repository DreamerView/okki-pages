<?php 

    header("Content-Type: application/json");

    $inputData = file_get_contents('php://input');
    $data = json_decode($inputData, true);

    if(isset($data['old-password'], $data['new-password'], $data['verify-password'], $data['user-uuid'])) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        require_once "_okki.php";
        $okki = new Okki();
        $db->start();

        try {
            $oldPassword = $data['old-password'];
            $newPassword = $data['new-password'];
            $verifyPassword = $data['verify-password'];
            $userUUID = htmlspecialchars($data['user-uuid']);

            $checkPasswordExist = $db->fetch("SELECT `password` FROM `users` WHERE `uuid`=?", [$userUUID]);

            if(!$checkPasswordExist) {
                exit(json_encode(['status' => "error", "error" => "user-not-exist"]));
            }

            if(password_verify($oldPassword, $checkPasswordExist['password'])) {
                if($newPassword === $verifyPassword) {
                    $password = password_hash($verifyPassword, PASSWORD_DEFAULT);
                    $db->execute("UPDATE `users` SET `password`=? WHERE `uuid`=?", [$password, $userUUID]);
                    $db->finish();
                    exit(json_encode(["status" => "success"]));
                } else {
                    exit(json_encode(["status" => "error", "error" => "verify-password-error"]));
                }
            } else {
                exit(json_encode(["status" => "error", "error" => "old-password-error"]));
            }

        } catch (Exception $e) {
            exit(json_encode(["status" => "error", "error" => "database-error"]));
        }
    } else {
        exit(json_encode(["status" => "error", "error" => "missing"]));
    }
?>
