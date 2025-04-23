<?php 
header('Content-Type: application/json; charset=utf-8');

if (isset($_POST['title']) && isset($_POST['article'])) {
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";
    $db = new SimpleDB();
    $db->start();
    try {
        include_once $_SERVER["DOCUMENT_ROOT"] . "/_globalEnvValues.php";

        $title = htmlspecialchars($_POST['title']);
        $article = htmlspecialchars($_POST['article']);

        // Выполнение запроса
        $result = $db->execute(
            "UPDATE `topics` SET `title` = AES_ENCRYPT(?, ?) WHERE `uuid` = ?",
            [$title, GLOBAL_ENCRYPT_KEY, $article]
        );
        $db->finish();

        // Ответ сервера
    
        exit(json_encode(['status' => "success"]));
    } catch (Exception $e) {
        $db->stop();
        // Обработка исключений
        exit(json_encode(['status' => "error", 'message' => $e->getMessage()]));
    }
} else {
    // Ошибка входных данных
    exit(json_encode(['status' => "error", 'message' => 'Missing title or article']));
}
?>