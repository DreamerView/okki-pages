<?php
    
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    
    $db = new SimpleDB();

    try {

        $inputData = file_get_contents('php://input');
        $data = json_decode($inputData, true);

        $input = $data['data'];
        
        if($data['type']==="multiple") {

            $followList = $db->fetchAll("SELECT `follower` FROM `follow_system` WHERE author=?", [$input["user_auth_uuid"]]);

            if (!empty($followList)) {
                $time = time();

                $db->query("SET autocommit = 1");

                foreach ($followList as $follow) {
                    $uuid = uniqid();
                    $check = $db->fetch("SELECT 1 FROM `notification` WHERE `from`=? AND `to`=? AND `url`=? AND `action`=? LIMIT 1", [
                        $input['user_auth_uuid'], 
                        $follow["follower"], 
                        $input['url'], 
                        $input['action']
                    ]);

                    if ($check) continue; // Пропускаем, если уже есть такое уведомление

                    $db->query(
                        "INSERT INTO `notification` (`uuid`, `from`, `to`, `action`, `url`, `time`, `read`) VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [$uuid, $input["user_auth_uuid"], $follow["follower"], $input['action'], $input['url'], $time, 0]
                    );

                    usleep(500000); // 0.5 секунды между запросами
                }

            }
            
        }

    } catch(Exception $e) {
        $db->stop();
        die($e->getMessage());
    } finally {
        $db->close();
    }
?>
