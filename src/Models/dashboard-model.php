<?php 

require_once __DIR__ . "Config/Database.php";

class Dashboard_model{

    private $conn;
    public function __construct(){

        $db = new Database();
        $this->conn = $db->connect();

    }

    public function getPendingResults(){

        $query = "SELECT * FROM samples WHERE status= 'Submitted'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;

    }
}

?>