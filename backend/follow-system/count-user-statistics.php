<?php

    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    try {
        if (empty($_GET['uuid']) || trim($_GET['uuid']) === '') {
            throw new Exception("UUID parameter is missing or empty");
        }

        $uuid = $_GET['uuid'];

        $countPage = $db->fetch("SELECT COUNT(*) AS `total` FROM `topics` WHERE `author` = ?",[$uuid]);
        $countFollower = $db->fetch("SELECT COUNT(*) AS `follower` FROM `follow_system` WHERE `author`=?",[$uuid]);
        $countFollowing = $db->fetch("SELECT COUNT(*) AS `following` FROM `follow_system` WHERE `follower`=?",[$uuid]);
        exit(json_encode(["status"=>"success","page"=>$countPage['total'],"follower"=>$countFollower['follower'],"following"=>$countFollowing["following"]]));

    } catch(Exception $e) {
        exit(json_encode(["status" => "error", "error" => $e->getMessage()]));
    } finally {
        $db->close();
    }

?>