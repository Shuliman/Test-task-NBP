<?php
namespace CurrencyConverter;

use PDO;
use PDOException;

class DatabaseConnection
{
    private $connection;
    private $serverName;
    private $database;
    private $username;
    private $password;
    private $options;
    private $tableName;

    public function __construct(array $config)
    {
        
        $this->serverName = $config['db']['host'];
        $this->database = $config['db']['dbname'];
        $this->username = $config['db']['username'];
        $this->password = $config['db']['password'];
        $this->options = $config['db']['options'];
        $this->tableName = $config['db']['tableName'];
        $this->connection = $this->makeConnection();
    }
    private function makeConnection(): PDO
    {
        try {
            return new PDO("mysql:host=$this->serverName;dbname=$this->database", $this->username, $this->password, $this->options);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function getTableName()
    {
        return $this->tableName;
    }
}
