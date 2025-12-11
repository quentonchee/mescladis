<?php
class Database {
    private $host = 'sqlite:' . __DIR__ . '/../../database.sqlite';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO($this->host);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
