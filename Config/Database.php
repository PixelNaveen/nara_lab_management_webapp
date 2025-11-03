<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $dbname = "lab";
    public $conn; // Added public property to store connection

    public function __construct() {
        // Auto-connect in constructor
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
    //     echo "<script>
    //    alert('Database is connected');
    //    </script>";
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    // Keep the connect method for backward compatibility if needed
    public function connect() {
        return $this->conn;
    }
}
?>