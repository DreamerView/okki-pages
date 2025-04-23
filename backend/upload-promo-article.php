<?php
header("Content-Type: application/json");

// Проверяем, есть ли файл
if (!isset($_FILES["image"]) || !isset($_POST["uuid"])) {
    echo json_encode(["success" => false, "error" => "Нет файла или UUID"]);
    exit;
}

$uuid = preg_replace("/[^a-z0-9-]/", "", strtolower($_POST["uuid"])); // Очистка UUID
$uploadDir = $_SERVER["DOCUMENT_ROOT"] . "/store/media/";
$fileName = $uuid . "/promo.webp";
$filePath = $uploadDir . $fileName;

// Создаём папку, если её нет
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Перемещаем файл
if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {
    echo json_encode(["success" => true, "file" => $filePath]);
} else {
    echo json_encode(["success" => false, "error" => "Ошибка при сохранении файла"]);
}
?>
