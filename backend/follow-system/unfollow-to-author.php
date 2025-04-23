<?php

    header("Content-Type: application/json");

    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();

    try {
        if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
            throw new Exception("Метод не разрешен", 405);
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data["author"])) {
            throw new Exception("Отсутствуют обязательные параметры", 400);
        }

        include_once $_SERVER["DOCUMENT_ROOT"] ."/backend/auth-on-frontend.php";

        if($user_auth_uuid === null) {
            throw new Exception("Need auth to system");
        }

        $db->start();

        $verify = $db->fetch("SELECT `uuid` FROM `follow_system` WHERE `author`=? AND `follower`=?",[$data['author'],$user_auth_uuid]);

        if(!$verify) {
            throw new Exception("Not followed to the author");
        }

        $db->query("DELETE FROM `follow_system` WHERE `author`=? AND `follower`=?",[$data['author'],$user_auth_uuid]);
        $db->finish();

        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        $db->stop();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    } finally {
        $db->close();
    }
?>
