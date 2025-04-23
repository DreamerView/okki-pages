<?php 
    header("Content-Type: application/json");
    
    if(isset($_GET['article'])) {
        include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        require_once "_okki.php";
        $okki = new Okki();
        $article = htmlspecialchars($_GET['article']);
        $get = $db->fetch("SELECT AES_DECRYPT(`title`,?) AS `title` FROM `topics` WHERE `uuid`=?",[GLOBAL_ENCRYPT_KEY,$article]);
        if($get) {
            $scheme = $db->fetchAll("SELECT `uuid`, `tag`,`tag_uuid`, `type` FROM `scheme` WHERE `uuid`=?",[$article]);
            $content = $db->fetchAll("SELECT `scheme_uuid`, `tag_uuid`,`component_uuid`, AES_DECRYPT(`content`,?) AS `content` FROM `content` WHERE `scheme_uuid`=?",[GLOBAL_ENCRYPT_KEY,$article]);
            $firstArray = $scheme;
            $secondArray = $content;
            foreach ($firstArray as &$item) {
                $tagUuid = $item['tag_uuid'];
                $type = $item['type'];

                // Фильтруем второй массив по совпадающему tag_uuid
                $matches = array_filter($secondArray, function($entry) use ($tagUuid) {
                    return $entry['tag_uuid'] === $tagUuid;
                });

                if ($type === 'single' && !empty($matches)) {
                    // Для single добавляем первый найденный content
                    $match = reset($matches); // Берем первый элемент
                    $item['content'] = $match['content'];
                } elseif ($type === 'multiple' && !empty($matches)) {
                    // Для multiple добавляем массив объектов в contents
                    $item['contents'] = array_values($matches); // Перенумеруем массив
                }
            }

            exit(json_encode(["status"=>"success","article_title"=>$get['title'],"array"=>$firstArray]));
        } else {
            exit(json_encode([
                "status"=>"error",
                "error"=>"Not found"
            ]));
        }
    }
?>