<?php 
    class Okki {
        public function aes256EncryptDecrypt($data, $encrypt = true) {
            // Генерация случайного IV
            $key = "844d58e25c6e2f832683f438cc34aab0070a185ecef324f8bc88516faae0422e";
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
            
            if ($encrypt) {
                // Шифрование данных
                $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
                
                // Добавление IV к зашифрованным данным для последующего дешифрования
                return base64_encode($iv . $encryptedData);  // Кодируем в base64 для передачи или сохранения
            } else {
                // Декодирование из base64
                $decodedData = base64_decode($data);
                
                // Извлечение IV из зашифрованных данных
                $iv = substr($decodedData, 0, openssl_cipher_iv_length('aes-256-cbc'));
                $encryptedData = substr($decodedData, openssl_cipher_iv_length('aes-256-cbc'));
                
                // Дешифрование данных
                return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
            }
        }

        public function SendNotification($type,$data) {
            
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

            // Получаем домен (без протокола)
            $host = $_SERVER['HTTP_HOST'];

            // Если нужен полный путь с доменом и текущей страницей
            $fullUrl = $protocol . $host;
            $ch = curl_init($fullUrl . "/backend/notification/server.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["type"=>$type,"data"=>$data]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Прерываем через 1 сек.
            curl_exec($ch);
            curl_close($ch);
        }
    }
?>