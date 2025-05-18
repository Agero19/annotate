<?php
class Database
{
    // Connection parameters
    private $host = "127.0.0.1";
    private $db_name = "annotatex";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getServerConnection()
    {
        // Connect to server only
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            die("❌ Connection to MySQL server failed: " . $e->getMessage());
        }
    }

    /**
     * Get the db connection object
     * @return PDO
     */
    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Error while connecting to db: " . $exception->getMessage();
        }

        return $this->conn;
    }

    /**
     * Close db connection
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
}