<?php
require_once __DIR__ . '/config.php';

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    public $db;
    public $statement;
    public function __construct()
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . '; charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        try {
            $this->db = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $ex) {
            http_response_code(500);
            echo $ex;
            exit();
        }
    }

    public function prepare($sql)
    {
        $this->statement = $this->db->prepare($sql);
        return $this;
    }

    public function bindAndExecute($values = [])
    {

        for ($i = 0; $i < count($values); $i += 2) {
            $this->statement->bindValue($values[$i], $values[$i + 1]);
        }
        $this->statement->execute();
        return $this;

    }

    public function getAll()
    {
        return $this->statement->fetchAll();
    }

    public function getOne()
    {
        return $this->statement->fetch();
    }
    public function rowCount()
    {
        return $this->statement->rowCount();
    }
    public function getLastId()
    {
        return $this->db->lastInsertId();
    }
}