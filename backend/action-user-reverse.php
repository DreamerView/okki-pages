<?php

    header("Content-Type: application/json");

    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();

    try {
        if ($_SERVER["REQUEST_METHOD"] !== "PUT") throw new Exception("Метод не разрешен", 405);

        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data["article"],$data['uuid'])) throw new Exception("Отсутствуют обязательные параметры", 400);

        include_once "auth-on-frontend.php";

        if($user_auth_uuid === null) throw new Exception("auth-required");

        if(!isset($data['article'],$data['type'])) throw new Exception("Missing arguments");

        $db->start();

        $verify = $data['type']==="repost"?
            $db->fetch("SELECT `uuid` FROM `repost` WHERE `author`=? AND `article`=?",[$user_auth_uuid,$data['article']])
            :
            $db->fetch("SELECT `uuid` FROM `saved` WHERE `author`=? AND `article`=?",[$user_auth_uuid,$data['article']]);

        if(!$verify) throw new Exception("Article not found");
    
        $data['type']==="repost"?
            $db->query("DELETE FROM `repost` WHERE `author`=? AND `article`=?",[$user_auth_uuid,$data['article']])
                :
            $db->query("DELETE FROM `saved` WHERE `author`=? AND `article`=?",[$user_auth_uuid,$data['article']]);
        
        $db->finish();

        echo json_encode(["status" => "success"]);
    } catch (Exception $e) {
        $db->stop();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    } finally {
        $db->close();
    }
?>
