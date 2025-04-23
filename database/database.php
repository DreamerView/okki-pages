<?php

class SimpleDB {
    private $mysqli;

    public function __construct() {
        $connect = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/mysql.json"), true);
        $connectHost = $connect['host'];
        $connectDatabase = $connect['database'];
        $connectUser = $connect['user'];
        $connectPassword = $connect['password'];

        // Подключаемся к базе данных через mysqli
        $this->mysqli = new mysqli($connectHost, $connectUser, $connectPassword, $connectDatabase);

        // Проверяем подключение
        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        // Устанавливаем кодировку
        $this->mysqli->set_charset("utf8");
    }

    // Выполнение запроса
    public function query($sql, $params = []) {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $this->mysqli->error);
        }

        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Определение типов данных для привязки
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        return $stmt;
    }

    // Получение всех данных
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Получение одной строки
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Выполнение запроса без получения результата
    public function execute($sql, $params = []) {
        $this->query($sql, $params);
    }

    // Получение последнего ID вставленной записи
    public function lastInsertId() {
        return $this->mysqli->insert_id;
    }

    // Закрытие соединения с базой данных
    public function close() {
        $this->mysqli->close();
    }

    // Чтение кэша
    public function readCache($filename) {
        if (file_exists($filename)) {
            $data = file_get_contents($filename);
            return json_decode($data, true); // Второй параметр true для возврата массива, а не объекта
        } else {
            return null; // Можно обработать ситуацию, когда файл не существует
        }
    }

    // Начало транзакции
    public function start() {
        $this->mysqli->begin_transaction();
    }

    // Подтверждение транзакции
    public function finish() {
        $this->mysqli->commit();
    }

    // Откат транзакции
    public function stop() {
        $this->mysqli->rollback();
    }

    // Получение переменной сервера
    public function getVariable($variableName) {
        $stmt = $this->query("SHOW VARIABLES LIKE ?", [$variableName]);
        return $stmt->get_result()->fetch_assoc()['Value'];
    }
}

?>
