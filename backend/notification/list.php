<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
$db = new SimpleDB();

try {
    include_once $_SERVER["DOCUMENT_ROOT"] ."/backend/auth-on-frontend.php";
    if(!$user_auth_uuid) throw new Exception("auth-required");

    $limit = 7;
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;

    $db->start();

    $lists = $db->fetchAll("
        SELECT AES_DECRYPT(u.`fullname`,?) AS `fullname`, 
            u.`login` AS `login`, 
            n.`action` AS `action`, 
            n.`time` AS `time`, 
            n.`uuid` AS `uuid`
        FROM `notification` n 
        JOIN `users` u ON u.`uuid`=n.`from` 
        WHERE n.`to`=? 
        ORDER BY n.`time` DESC 
        LIMIT ? OFFSET ?", 
        [GLOBAL_ENCRYPT_KEY, $user_auth_uuid, $limit, $offset]
    );

    $newList = [];
    $notificationIds = [];

    foreach ($lists as $list) {
        $placeholder = ($list['action'] == "1") ? "followed you" : "";
        $notificationIds[] = $list['uuid']; // Собираем ID уведомлений

        $newList[] = [
            "fullname" => $list["fullname"],
            "login" => $list["login"],
            "image" => "/backend/image.php?fullname=" . $list['fullname'],
            "placeholder" => $placeholder,
            "date" => date("H:i, d.m.Y", $list['time'])
        ];
    }


    if (!empty($notificationIds)) {
        $placeholders = implode(",", array_fill(0, count($notificationIds), "?"));
        $sql = "UPDATE `notification` SET `read`=1 WHERE `uuid` IN ($placeholders)";    
        $db->execute($sql, $notificationIds);
    }

    $db->finish();


    echo json_encode(["status" => "success", "list" => $newList]);
} catch(Exception $e) {
    $db->stop();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    $db->close();
}
?>
