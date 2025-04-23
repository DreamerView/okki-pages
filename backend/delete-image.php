<?php 
    require_once $_SERVER["DOCUMENT_ROOT"] . "/database.php";

    $db = new SimpleDB();

    try {
        // Проверка, был ли запрос методом DELETE
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            throw new Exception("Invalid request method. Please use DELETE.");
        }

        // Получаем данные из тела запроса
        $data = json_decode(file_get_contents("php://input"), true);

        // Проверка на пустое значение article
        if (empty($data['article']) || trim($data['article']) === '') {
            throw new Exception("Article parameter is missing or empty.");
        }

        $article = $data['article'];

        // Подключаем необходимые файлы
        
        // Проверка, существует ли такая тема в базе
        $topic = $db->fetch("SELECT `uuid` FROM `topics` WHERE `uuid` = ?", [$article]);

        if (!$topic) {
            throw new Exception("Topic not found in the database.");
        }

        $db->start();

        // Удаляем данные из связанных таблиц
        $db->query("DELETE FROM `topics` WHERE `uuid` = ?", [$article]);
        $db->query("DELETE FROM `scheme` WHERE `uuid` = ?", [$article]);
        $db->query("DELETE FROM `content` WHERE `scheme_uuid` = ?", [$article]);

        // Завершаем транзакцию
        $db->finish();

        // Возвращаем успешный ответ
        exit(json_encode(["status" => "success"]));

    } catch (Exception $e) {
        // В случае ошибки откатываем транзакцию и выводим сообщение об ошибке
        $db->stop();
        exit(json_encode(["status" => "error", "message" => $e->getMessage()]));
    }
?>