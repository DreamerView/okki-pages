<?php
    
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    
    $db = new SimpleDB();

    try {

        $inputData = file_get_contents('php://input');
        $data = json_decode($inputData, true);
        
        $db->start();

        $followList = $db->fetchAll("SELECT `follower` FROM `follow_system` WHERE author=?", [$data["user_auth_uuid"]]);

        if (!empty($followList)) {
            $time = time();
            $action = $data["type"] === "repost" ? 2 : 3;

            foreach ($followList as $follow) {
                $uuid = uniqid();
                $db->query(
                    "INSERT INTO `notification` (`uuid`, `from`, `to`, `action`, `url`, `time`, `read`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                    [$uuid, $data["user_auth_uuid"], $follow["follower"], $action, "/a/@" . $data["article"], $time, 0]
                );
                sleep(1);
            }
        }

        $db->finish();
    } catch(Exception $e) {
        $db->stop();
        die($e->getMessage());
    } finally {
        $db->close();
    }
?>
