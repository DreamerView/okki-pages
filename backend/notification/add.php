<?php 

    function addToNotification($from,$to,$action,$url) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        try {
            if(!isset($from,$to, $action, $url)) throw new Exception("Missing arguments");
            $uuid = uniqid();
            $time = time();
            $db->start();
            $db->query("INSERT INTO `notification` (`uuid`, `from`, `to`, `action`, `url`, `time`, `read`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$uuid, $from, $to, $action, $url, $time, 0]);
            $db->finish();
            return true;
        } catch(Exception $e) {
            $db->stop();
            return false;
        } finally {
            $db->close();
        }
    }

    function addToNotificationMultiple($array = []) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        try {
            if(!is_array($array)) throw new Exception("Missing arguments");
            $db->start();
            foreach($array as $list) {
                $uuid = uniqid();
                $time = time();
                $db->query("INSERT INTO `notification` (`uuid`, `from`, `to`, `action`, `url`, `time`, `read`) VALUES (?, ?, ?, ?, ?, ?, ?);", [$uuid, $list['from'], $list['to'], $list['action'], $list['url'], $time, 0]);
                sleep(5000);
            }
            $db->finish();
            return true;
        } catch(Exception $e) {
            $db->stop();
            return false;
        } finally {
            $db->close();
        }
    }

?>