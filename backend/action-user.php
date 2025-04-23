<?php
    
header("Content-Type: application/json");

require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
$db = new SimpleDB();

require_once "_okki.php";
$okki = new Okki();

try {
    if ($_SERVER["REQUEST_METHOD"] !== "PUT") throw new Exception("Метод не разрешен", 405);

    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data["article"], $data["uuid"], $data["type"])) throw new Exception("Отсутствуют обязательные параметры", 400);

    include_once "auth-on-frontend.php";

    if ($user_auth_uuid === null) throw new Exception("auth-required");

    $db->start();

    $verify = $data["type"] === "repost" ?
        $db->fetch("SELECT `uuid` FROM `repost` WHERE `author`=? AND `article`=?", [$user_auth_uuid, $data["article"]]) :
        $db->fetch("SELECT `uuid` FROM `saved` WHERE `author`=? AND `article`=?", [$user_auth_uuid, $data["article"]]);

    if ($verify) throw new Exception("Article already has");

    $db->query(
        $data["type"] === "repost"
            ? "INSERT INTO `repost` (`uuid`, `author`, `article`, `time`) VALUES (?, ?, ?, ?)"
            : "INSERT INTO `saved` (`uuid`, `author`, `article`, `time`) VALUES (?, ?, ?, ?)",
        [$data["uuid"], $user_auth_uuid, $data["article"], time()]
    );

    $db->finish();

    if($data["type"] === "repost") {
        $array = ["user_auth_uuid"=>$user_auth_uuid,"url"=>"/a/@" . $data["article"],"action"=>"2"];
        $okki->SendNotification("multiple",$array);
    }

    // ✅ Отправляем ответ клиенту СРАЗУ
    echo json_encode(["status" => "success"]);

} catch (Exception $e) {
    $db->stop();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    $db->close();
}
