<?php 
require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
$db = new SimpleDB();

try {
    if(!isset($_GET['uuid'],$_GET['type'],$_GET['offsetFollowList'])) {
        throw new Exception("Missing arguments");
    }

    include_once $_SERVER["DOCUMENT_ROOT"] . "/backend/auth-on-frontend.php";

    if(!$user_auth_uuid) throw new Exception("auth-required");

    $uuid = $_GET['uuid'];
    $type = $_GET['type'];
    $offsetFollowList = intval($_GET['offsetFollowList']);
    $limit = 7;

    if($type === "author") {
        $list = $db->fetchAll("SELECT 
            f.follower AS uuid, 
            AES_DECRYPT(u.fullname, ?) AS fullname, 
            f.author,
            u.login AS login,
            CONCAT('/backend/image.php?size=48&&fullname=', AES_DECRYPT(u.fullname, ?)) AS `image`,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM follow_system fs 
                    WHERE fs.author = f.follower 
                    AND fs.follower = ?
                ) THEN 1 
                ELSE 0 
            END AS is_following
        FROM follow_system f
        JOIN users u ON u.uuid = f.follower
        WHERE f.author = ?
        LIMIT ? OFFSET ?
        ;", [GLOBAL_ENCRYPT_KEY, GLOBAL_ENCRYPT_KEY, $user_auth_uuid, $uuid, $limit, $offsetFollowList]);
    } else {
        $list = $db->fetchAll("SELECT 
            f.author AS uuid, 
            AES_DECRYPT(u.fullname, ?) AS fullname, 
            f.follower,
            u.login AS login,
            CONCAT('/backend/image.php?size=48&&fullname=', AES_DECRYPT(u.fullname, ?)) AS `image`,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM follow_system fs 
                    WHERE fs.follower = ? 
                    AND fs.author = f.author
                ) THEN 1 
                ELSE 0 
            END AS is_following
        FROM follow_system f
        JOIN users u ON u.uuid = f.author
        WHERE f.follower = ?
        LIMIT ? OFFSET ?
        ;", [GLOBAL_ENCRYPT_KEY, GLOBAL_ENCRYPT_KEY, $user_auth_uuid, $uuid, $limit, $offsetFollowList]);
    }

    echo json_encode(["status" => "success", "list" => $list, "uuid" => $user_auth_uuid]);
} catch(Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    $db->close();
}
?>
