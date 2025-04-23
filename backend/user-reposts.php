<?php
    header("Content-Type: application/json");
    
    try {
        require_once "auth-on-frontend.php";

        if (empty($_GET['login']) || trim($_GET['login']) === '') {
            throw new Exception("Login parameter is missing or empty");
        }

        $login = $_GET['login'];

        include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";

        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";

        $db = new SimpleDB();
        $user = $db->fetch("SELECT `uuid` FROM `users` WHERE `login` = ?", [$login]);

        if (!$user) {
            throw new Exception("User not found");
        }

        // Получаем темы пользователя
        $topics = $db->fetchAll("
            SELECT r.`time`,t.`uuid`, AES_DECRYPT(t.`title`, ?) AS `title` 
            FROM `repost` r 
            JOIN `topics` t ON t.`uuid` = r.`article`
            WHERE r.`author` = ? 
            ORDER BY r.`time` DESC
        ", [GLOBAL_ENCRYPT_KEY, $user['uuid']]);

        $edit = false;

        if($user_auth_uuid === $user['uuid']) {
            $edit = true;
        }

        $decodedTopics = array_map(function ($topic) {
            return [
                "uuid" => $topic['uuid'],
                "title" => $topic['title'],
                "time" => $topic['time'],
                "date" => date('d.m.Y H:i', $topic['time'])
            ];
        }, $topics);

        exit(json_encode(["repost" => 1,"edit"=>$edit ,"data" => $decodedTopics]));
    } catch (Exception $e) {
        // Возвращаем ошибку в случае исключения
        exit(json_encode(["repost" => 0, "error" => $e->getMessage()]));
    }
?>