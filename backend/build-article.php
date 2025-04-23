<?php

    header("Content-Type: application/json");

    function isPathInArray($array, $searchPath) {
        foreach ($array as $item) {
            if (isset($item['content']) && $item['content'] === $searchPath) {
                return true; // ✅ Путь найден
            }
        }
        return false; // ❌ Путь отсутствует
    }

    function findAllTrashImages(array $data): array {
        $result = [];
        foreach ($data as $item) {
            if (isset($item['tag'], $item['content']) && 
                $item['tag'] === 'image' && 
                strpos($item['content'], '/trash') !== false) {
                $result[] = $item;
            }
        }
        return $result;
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    // Получаем домен (без протокола)
    $host = $_SERVER['HTTP_HOST'];

    // Если нужен полный путь с доменом и текущей страницей
    $fullUrl = $protocol . $host;
    // Получаем данные из тела запроса
    $data = json_decode(file_get_contents("php://input"), true);

    // Проверяем, были ли данные
    if ($data) {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();
        require_once "_okki.php";
        $okki = new Okki();
        $db->start();
        try {
            include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
            $scheme = $data['scheme'];
            $content = $data['content'];
            $uuid = $data['uuid'];
            $directory = $_SERVER["DOCUMENT_ROOT"] . "/store/media/" . $uuid;

            if(!file_exists($directory)) mkdir($directory);

            foreach ($content as $key => $item) {
                if (
                    isset($item['tag'], $item['content']) &&
                    $item['tag'] === 'image' &&
                    strpos($item['content'], '/trash') !== false
                ) {
                    $listImageName = basename($item['content']);
                    $listImageDirectory = $_SERVER["DOCUMENT_ROOT"] . $item['content'];
                    $listNewImageDirectory = $_SERVER["DOCUMENT_ROOT"] . "/store/media/". $uuid . "/" . $listImageName;
                    // Перемещаем файл
                    if(rename($listImageDirectory, $listNewImageDirectory)){
                        // Обновляем значение 'content' на новый путь (относительный к DOCUMENT_ROOT)
                        $content[$key]['content'] = "/store/media/". $uuid . "/" . $listImageName;
                    } else {
                        throw new Exception("Ошибка при перемещении файла: $listImageDirectory");
                    }
                }
            }

            $check = $db->fetchAll("SELECT AES_DECRYPT(`content`, ?) AS `img` FROM `content` WHERE `scheme_uuid`=? AND `tag`='image' ",[GLOBAL_ENCRYPT_KEY,$uuid]);
            if(!empty($check)) {
                foreach($check as $list) {
                    $result = isPathInArray($content,$list['img']);
                    $newPath = $_SERVER["DOCUMENT_ROOT"] . $list['img'];
                    if(!$result && file_exists($newPath)) unlink($newPath); 
                }
            }
            $db->execute("DELETE FROM `scheme` WHERE `uuid`=?",[$uuid]);
            $db->execute("DELETE FROM `content` WHERE `scheme_uuid`=?",[$uuid]);
            if (!empty($scheme)) {
                $ph = $params = [];
                foreach ($scheme as $html) {
                    $ph[] = "(?, ?, ?, ?)";
                    $params = array_merge($params, [
                        $html['uuid'],
                        $html['tag'],
                        $html['tag_uuid'],
                        $html['type']
                    ]);
                }
                $db->execute("INSERT INTO `scheme` (`uuid`, `tag`, `tag_uuid`, `type`) VALUES " . implode(", ", $ph), $params);
            }

            if (!empty($content)) {
                $ph = $params = [];
                foreach ($content as $html) {
                    $ph[] = "(?, ?, ?, ?, AES_ENCRYPT(?, ?))";
                    $params = array_merge($params, [
                        $html['scheme_uuid'],
                        $html['tag'],
                        $html['tag_uuid'],
                        $html['component_uuid'],
                        $html['content'],
                        GLOBAL_ENCRYPT_KEY
                    ]);
                }
                $db->execute("INSERT INTO `content` (`scheme_uuid`, `tag`, `tag_uuid`, `component_uuid`, `content`) VALUES " . implode(", ", $ph), $params);
            }
            $db->finish();
            $html = file_get_contents($fullUrl . "/article/preview?article=" . $uuid);
            file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/store/cache/" . $uuid . ".html",$html);
            
            exit(json_encode(["status"=>"success","preview"=>$fullUrl]));
        } catch(error) {
            $db->stop();
            echo json_encode([
                "status" => "error",
                "message" => "Database error"
            ]);
        } finally {
            $db->close();
        }
    } else {
        // Если данных нет
        echo json_encode([
            "status" => "error",
            "message" => "No data received"
        ]);
    }
?>
