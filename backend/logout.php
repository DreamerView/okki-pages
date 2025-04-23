<?php
    header("Content-Type: application/json");
    
    try {
        setcookie(
            "0auth", // Имя cookie
            "", // Пустое значение
            [
                'expires' => time() - 3600, // Устанавливаем время истечения в прошлом
                'path' => '/', // Путь должен совпадать с тем, что использовался при создании cookie
                'domain' => '', // Домен должен совпадать с тем, что использовался при создании cookie
                'secure' => true, // Устанавливаем так же, как было при создании (если HTTPS — true)
                'httponly' => true, // Устанавливаем так же, как было при создании
                'samesite' => 'Strict' // Устанавливаем так же, как было при создании
            ]
        );
        exit(json_encode(["status"=>"success"]));
    } catch(error) {
        exit(json_encode(["status"=>"error"]));
    }
?>