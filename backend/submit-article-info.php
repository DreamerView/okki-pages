<?php
    header('Content-Type: application/json');
    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/backend/_okki.php";
    $db = new SimpleDB();
    $okki = new Okki();
    try {
        if ($_SERVER["REQUEST_METHOD"] !== "PUT") {
            throw new Exception("Метод не разрешен", 405);
        }

        if (stripos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
            throw new Exception("Invalid Content-Type, expected application/json");
        }

        $data = json_decode(file_get_contents("php://input"), true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON input");
        }

        if (!isset($data['title'], $data['description'], $data['view'], $data['uuid'])) {
            throw new Exception("Missing arguments");
        }

        include_once "auth-on-frontend.php";
        if ($user_auth_uuid === null) {
            throw new Exception("auth-required");
        }

        $db->start();

        $flag = $db->execute(
            "UPDATE `topics` SET `title` = AES_ENCRYPT(?,?), `description` = AES_ENCRYPT(?,?), `status`=? WHERE `uuid`=? AND `author`=?", 
            [$data['title'], GLOBAL_ENCRYPT_KEY, $data['description'], GLOBAL_ENCRYPT_KEY, $data['view'], $data['uuid'], $user_auth_uuid]
        );

        $db->finish();

        if($data['view']==="0") {
            $array = ["user_auth_uuid"=>$user_auth_uuid,"url"=>"/a/@" . $data['uuid'],"action"=>"4"];
            $okki->SendNotification("multiple",$array);
        }

        exit(json_encode(["status" => "success","flag"=>GLOBAL_ENCRYPT_KEY]));

    } catch (Exception $e) {
        $db->stop();
        exit(json_encode(["status" => "error", "message" => $e->getMessage()]));
    } finally {
        $db->close();
    }
?>
