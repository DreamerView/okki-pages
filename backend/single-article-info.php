<?php
    header('Content-Type: application/json');
    try {
        if (!isset($_GET['article'])) {
            throw new Exception("Article not found");
        }
        $article = $_GET['article'];
        
        include_once "auth-on-frontend.php";

        if ($user_auth_uuid === null) {
            throw new Exception("auth-required");
        }

        include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
        $db = new SimpleDB();

        $get = $db->fetch("SELECT AES_DECRYPT(`title`,?) AS `title`, AES_DECRYPT(`description`,?) AS `description`, `status` FROM `topics` WHERE `uuid` = ?", [GLOBAL_ENCRYPT_KEY,GLOBAL_ENCRYPT_KEY,$article]);

        if (!$get) { // Проверяем, вернулось ли что-то
            throw new Exception("Not found article info");
        }

        $image = "/source/image/placeholder.webp";

        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/store/media/" . $article . "/promo.webp")) $image = "/store/media/" . $article . "/promo.webp";

        exit(json_encode(["status" => "success","title"=>$get['title'],"description"=>$get['description'],"view"=>$get['status'], "image"=>$image]));

    } catch (Exception $e) {
        exit(json_encode(["status" => "error", "message" => $e->getMessage()]));
    }
?>