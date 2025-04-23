<?php 
    header("Content-Type: application/json");
    define("ROOT_DIR",$_SERVER["DOCUMENT_ROOT"]);
    try {
        if(!isset($_GET['search']) || empty($_GET['search'])) {
            throw new Exception("Missing arguments");
        }
        
        $search = $_GET['search'];

        require_once ROOT_DIR . "/database.php";
        include_once ROOT_DIR . "/_globalEnvValues.php";

        $db = new SimpleDB();

        $data = $db->fetchAll(
            "SELECT c.`scheme_uuid` AS `uuid`, CAST(AES_DECRYPT(t.`title`, ?) AS CHAR) AS `title`
            FROM `content` c
            JOIN `topics` t ON c.scheme_uuid = t.uuid
            WHERE LOWER(CAST(AES_DECRYPT(c.`content`, ?) AS CHAR)) LIKE CONCAT('%', LOWER(?), '%')
            GROUP BY c.scheme_uuid
            LIMIT 5",
            [GLOBAL_ENCRYPT_KEY, GLOBAL_ENCRYPT_KEY, $search]
        );



        $db->close();
        echo json_encode(["status"=>"success","data"=>$data]);
    } catch(Exception $e) {
        echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
    }
?>