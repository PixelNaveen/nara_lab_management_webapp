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

    /**
     * Get individual price by ID
     */

    public function getIndividualPricesById($pricing_id)
    {
        $sql = "SELECT pp.*, tp.parameter_name, tp.parameter_code 
        FROM parameter_pricing pp 
        INNER JOIN test_parameters tp ON pp.parameter_id = tp.parameter_id 
        WHERE pp.pricing_id = ? AND pp.is_deleted = 0";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $pricing_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Check if parameter already has pricing
     */

    public function hasIndividualPrice($parameter_id, $exclude_pricing_id = null)
    {
        $sql = "SELECT pricing_id FROM parameter_pricing WHERE parameter_id = ? AND is_deleted = 0";

        $params = [$parameter_id];
        $types = "i";

        if ($exclude_pricing_id) {
            $sql .= " AND pricing_id != ?";
            $params[] = $exclude_pricing_id;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$parameter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Find deleted individual price by parameter_id
     */

    public function findDeletedIndividualPrice($param_id)
    {
        $sql = "SELECT pricing_id FROM parameter_pricing
        WHERE parameter_id = ? AND is_deleted = 1 
        LIMIT 1";
        $stmt = $this->conn->prepare($sql);

        $stmt->bind_param("i", $param_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Insert individual price
     */

    public function insertIndividualPrice($parameter_id, $test_charge, $is_active = 1)
    {

        $sql = "INSERT INTO parameter_pricing(parameter_id, test_charge, is_active, is_deleted,created_at) 
        VALUES (?, ?, ?, 0, NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("idi", $parameter_id, $test_charge, $is_active);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    /**
     * Reactivate deleted individual price
     */

     public function reactivateIndividualPrice($pricing_id, $test_charge, $is_active)
    {
        $stmt = $this->conn->prepare(
            "UPDATE parameter_pricing 
            SET test_charge = ?, is_active = ?, is_deleted = 0, updated_at = NOW()
            WHERE pricing_id = ?"
        );
        $stmt->bind_param("dii", $test_charge, $is_active, $pricing_id);
        return $stmt->execute();
    }

}
