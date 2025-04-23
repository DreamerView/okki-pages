<?php
    header("Content-Type: application/json");
    if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = $_SERVER["DOCUMENT_ROOT"] . '/store/trash/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filename = uniqid('img_') . '.webp';
        $filePath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
            echo json_encode(['success' => true, 'filepath' => '/store/trash/' . $filename]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка при сохранении файла']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Ошибка загрузки файла']);
    }
?>
