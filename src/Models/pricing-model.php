<?php
require_once __DIR__ . '/../../Config/Database.php';

class PricingModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    }

    // ============= Individual Pricing =================

    /**
     * Get all individual prices with filters
     */

    public function getAllIndividualPrices($filters = [])
    {
        $sql = "SELECT 
                pp.pricing_id,
                pp.parameter_id,
                pp.test_charge,
                pp.is_active,
                pp.created_at,
                pp.parameter_name,
                pp.parameter_code
                FROM parameter_pricing pp
                INNER JOIN test_parameters tp ON 
                pp.parameter_id = tp.parameter_id WHERE pp.is_deleted = 0 AND tp.is_deleted = 0
                ";

        $params = [];
        $types =  "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND pp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND tp.parameter_name LIKE ?";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $types .= "s";
        }

        $sql .= " ORDER BY tp.parameter_name ASC";

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $prices = [];
        while ($row = $result->fetch_assoc()) {
            $prices[] = $row;
        }
        return $prices;
    }
}
