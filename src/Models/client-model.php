<?php

require_once __DIR__ . "../../Config/Database.php";

class Client_model
{
    private $db;


    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getAll()
    {
        $result = $this->db->conn->query("SELECT * FROM clients ORDER BY id ASC");
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function getById($id)
    {
        $stmt = $this->db->conn->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->bind_param("id", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

  public function insert($data)
    {
        $stmt = $this->db->conn->prepare("INSERT INTO clients (client_name, address_line1, city, phone_primary, contact_person) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $data['clientName'], $data['address'], $data['city'], $data['phoneNo'], $data['contactPerson']);
        if ($stmt->execute()) {
            return $stmt->insert_id;
        }
        return 0;
    }
}
