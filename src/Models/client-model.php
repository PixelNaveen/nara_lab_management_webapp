<?php
require_once __DIR__ . '/../../Config/Database.php';

class ClientModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    // =================== GET ALL ACTIVE CLIENTS ===================
    public function getAllClients()
    {
        $sql = "SELECT client_id, client_name, address_line1, city, phone_primary, contact_person, registration_date 
                FROM clients WHERE is_active = 1 ORDER BY client_id DESC";
        $result = $this->conn->query($sql);
        $clients = [];
        while ($row = $result->fetch_assoc()) {
            $clients[] = $row;
        }
        return $clients;
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($name, $phone)
    {
        $stmt = $this->conn->prepare("SELECT client_id FROM clients WHERE (client_name = ? OR phone_primary = ?) AND is_active = 1");
        $stmt->bind_param("ss", $name, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== INSERT ===================
    public function insertClient($name, $address, $city, $phone, $contact)
    {
        $stmt = $this->conn->prepare("INSERT INTO clients (client_name, address_line1, city, phone_primary, contact_person, registration_date, is_active)
                                      VALUES (?, ?, ?, ?, ?, NOW(), 1)");
        $stmt->bind_param("sssss", $name, $address, $city, $phone, $contact);
        return $stmt->execute();
    }

    // =================== UPDATE ===================
    public function updateClient($id, $name, $address, $city, $phone, $contact)
    {
        $stmt = $this->conn->prepare("UPDATE clients 
                                      SET client_name = ?, address_line1 = ?, city = ?, phone_primary = ?, contact_person = ?
                                      WHERE client_id = ?");
        $stmt->bind_param("sssssi", $name, $address, $city, $phone, $contact, $id);
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteClient($id)
    {
        $stmt = $this->conn->prepare("UPDATE clients SET is_active = 0 WHERE client_id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
