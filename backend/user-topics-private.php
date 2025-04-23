<?php
    header("Content-Type: application/json");
    try {
        require_once "auth-on-frontend.php";

        include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";

        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";

        $db = new SimpleDB();

        include_once "auth-on-frontend.php";

        if($user_auth_uuid === null) {
            throw new Exception("auth-required");
        }

        $user = $db->fetch("SELECT `uuid` FROM `users` WHERE `uuid` = ?", [$user_auth_uuid]);

        if (!$user) {
            throw new Exception("User not found");
        }

        // Получаем темы пользователя
        $topics = $db->fetchAll("SELECT `uuid`, AES_DECRYPT(`title`,?) AS `title`, `status`, `time` FROM `topics` WHERE `author` = ? AND `status`=1 ORDER BY `time` DESC", [GLOBAL_ENCRYPT_KEY, $user['uuid']]);

        $edit = false;

        if($user_auth_uuid === $user['uuid']) {
            $edit = true;
        }

        $decodedTopics = array_map(function ($topic) {
            return [
                "uuid" => $topic['uuid'],
                "title" => $topic['title'],
                "status" => $topic['status'],
                "time" => $topic['time'],
                "date" => date('d.m.Y H:i', $topic['time'])
            ];
        }, $topics);

        exit(json_encode(["private" => 1,"edit"=>$edit ,"data" => $decodedTopics]));
    } catch (Exception $e) {
        // Возвращаем ошибку в случае исключения
        exit(json_encode(["private" => 0, "error" => $e->getMessage()]));
    }
?>
