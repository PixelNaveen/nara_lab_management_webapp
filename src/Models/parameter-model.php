<?php

require_once __DIR__ . '/../../Config/Database.php';

class ParameterModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
        $this->conn->set_charset("utf8mb4");
    } 
    // ============= GET ALL PARAMETERS WITH PAGINATION (Pagination not used) =============
    public function getAllParameters($filters = [])
    {
        $sql = "SELECT 
                tp.parameter_id,
                tp.parameter_code,
                tp.parameter_name,
                tp.parameter_category,
                tp.base_unit,
                tp.has_variants,
                tp.swab_enabled,
                tp.is_active, 
                tp.created_at,
                 COUNT(DISTINCT pv.variant_id) as variant_count,
                    GROUP_CONCAT(DISTINCT tm.method_name ORDER BY pm.sequence_order, tm.method_name SEPARATOR ', ') as methods
                FROM test_parameters tp
                LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id 
                    AND pv.is_active = 1 AND pv.is_deleted = 0
                LEFT JOIN parameter_methods pm ON tp.parameter_id = pm.parameter_id
                LEFT JOIN test_methods tm ON pm.method_id = tm.method_id AND tm.is_active = 1 AND tm.is_deleted = 0
                WHERE tp.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= "AND tp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($fiters['search']) && $filters['search'] !== '') {
            $sql .= " AND (tp.parameter_name LIKE ? OR tp.parameter_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        $sql .= " GROUP BY tp.parameter_id ORDER BY tp.parameter.id ASC";

        // Get total count 
        $countSql = "SELECT COUNT(*) AS total FROM (" . $sql . ") as counted";

        if (!empty($params)) {
            $stmtCount = $this->conn->prepare($countSql);
            $stmtCount->bind_param($types, ...$params);
            $stmtCount->execute();
            $countResult = $stmtCount->get_result();
            $total = $countResult->fetch_assoc()['total'];
        } else {
            $countResult = $this->conn->query($countSql);
            $total = $countResult->fetch_assoc()['total'];
        }
        // Add pagination
        $page = isset($filters['page']) ? intval($filters['page']) : 1;
        $limit = isset($filters['limit']) ? intval($filters['limit']) : 50;
        $offset = ($page - 1) * $limit;

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        if(!empty($params)){
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $parameters = [];
        while($row = $result->fetch_assoc()){
            $parameters[] = $row;
        }

        return[
            'data' => $parameters,
            'total' => $total
        ];
    }
}
