<?php

require_once __DIR__ . "/../../Config/Database.php";

class DashboardModel
{

    private $conn;
    public function __construct()
    {

        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getCategoryDistribution($dateFrom, $dateTo)
    {
        $query = "SELECT 
                    COUNT(DISTINCT CASE WHEN s.submission_type = 'swab' OR si.sample_category_id = 3 THEN s.sample_id END) AS swab_count,
                    COUNT(DISTINCT CASE WHEN s.submission_type = 'regular' AND si.sample_category_id = 1 THEN s.sample_id END) AS water_count,
                    COUNT(DISTINCT CASE WHEN s.submission_type = 'regular' AND si.sample_category_id = 2 THEN s.sample_id END) AS food_count
                  FROM samples s
                  LEFT JOIN sample_items si ON si.sample_id = s.sample_id
                  WHERE DATE(s.received_date) BETWEEN ? AND ? 
                  AND s.status != 'Cancelled'";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ss', $dateFrom, $dateTo);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            ['category' => 'Water', 'count' => (int)($result['water_count'] ?? 0)],
            ['category' => 'Food', 'count' => (int)($result['food_count'] ?? 0)],
            ['category' => 'Swab', 'count' => (int)($result['swab_count'] ?? 0)]
        ];
    }

    public function getPopularTests($dateFrom, $dateTo, $limit = 5)
    {
        // Query sample_tests and join with test_parameters
        // We link sample_tests -> sample_items -> samples to check date
        $query = "SELECT tp.parameter_name, COUNT(st.sample_test_id) as request_count
                  FROM sample_tests st
                  JOIN test_parameters tp ON st.parameter_id = tp.parameter_id
                  JOIN sample_items si ON st.sample_item_id = si.sample_item_id
                  JOIN samples s ON si.sample_id = s.sample_id
                  WHERE DATE(s.received_date) BETWEEN ? AND ? 
                  AND s.status != 'Cancelled'
                  GROUP BY tp.parameter_name
                  ORDER BY request_count DESC
                  LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('ssi', $dateFrom, $dateTo, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $data;
    }
}
