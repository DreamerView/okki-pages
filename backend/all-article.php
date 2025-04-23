<?php

    header("Content-Type: application/json");
    define("DOCUMENT_ROOT",$_SERVER["DOCUMENT_ROOT"]);
    include_once DOCUMENT_ROOT . "/_globalEnvValues.php";
    require_once DOCUMENT_ROOT . "/database.php";

    $db = new SimpleDB();

    try {
        $get = $db->fetchAll("SELECT AES_DECRYPT(`title`,?) AS `title`, `uuid` FROM `topics` WHERE `status`=0", [GLOBAL_ENCRYPT_KEY]);
        echo json_encode($get ? ["status" => "success", "array" => $get] : ["status" => "error", "message" => "No data found"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    } finally {
        $db->close();
    }
    
?>