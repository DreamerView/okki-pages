<?php 

    header("Content-Type: application/json");

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    // Получаем домен (без протокола)
    $host = $_SERVER['HTTP_HOST'];

    // Если нужен полный путь с доменом и текущей страницей
    $fullUrl = $protocol . $host;

    
    include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();

    try {
        
        if(!isset($_POST['uuid'],$_POST['title'],$_POST['status'],$_POST['description'])) throw new Exception("Отсутствуют обязательные параметры");

        include_once "auth-on-frontend.php";

        if($user_auth_uuid === null) throw new Exception("Need auth to system");

        $uuid = $_POST['uuid'];

        $title = $_POST['title'];
        $description = $_POST['description'];
        $status = $_POST['status'];

        $db->start();
        $db->query("INSERT INTO `topics` (`uuid`, `title`,`description`, `author`, `time`, `status`) VALUES (?, AES_ENCRYPT(?,?),AES_ENCRYPT(?,?), ?, ?, ?);",[$uuid,$title,GLOBAL_ENCRYPT_KEY,$description,GLOBAL_ENCRYPT_KEY,$user_auth_uuid,time(),$status]);
        $db->finish();

        $html = file_get_contents($fullUrl . "/article/preview?article=" . $uuid);
        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/store/cache/" . $uuid . ".html",$html);


        $createFolder = $_SERVER["DOCUMENT_ROOT"] . "/store/media/" . $uuid;
        if(!file_exists($createFolder)) mkdir($createFolder);

        if($status==="0") {
            $array = ["user_auth_uuid"=>$user_auth_uuid,"url"=>"/a/@" . $uuid,"action"=>"3"];
            $okki->SendNotification("multiple",$array);
        }

        echo json_encode(['status'=>"success","uuid"=>$uuid]);
    } catch(Exception $e) {
        $db->stop();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    } finally {
        $db->close();
    }
?>