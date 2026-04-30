<?php

/**
 * Turnaround Time (TAT) Report Model
 * Laboratory Management System
 *
 * Handles database queries for TAT analytics:
 * - Summary KPIs (avg TAT, completed count, breach count)
 * - Detailed sample-level TAT rows
 * - Status distribution counts
 *
 * TAT = DATEDIFF(analysis_end_date, received_date) for completed samples
 *
 * @version 1.0
 */

require_once __DIR__ . '/../../Config/Database.php';

class TurnaroundModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Get TAT summary KPIs.
     *
     * @param array $filters  date_from, date_to, status
     * @return array  avg_tat, completed_count, breached_count, on_time_count, pending_count
     */
    public function getSummary($filters = [])
    {
        $where = $this->buildWhereClause($filters);
        $params = $where['params'];
        $types = $where['types'];
        $clause = $where['clause'];

        $sql = "SELECT
                    COUNT(CASE WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL THEN 1 END) AS completed_count,
                    ROUND(AVG(
                        CASE WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL
                             THEN DATEDIFF(s.analysis_end_date, s.received_date)
                        END
                    ), 1) AS avg_tat,
                    COUNT(CASE WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL
                               AND s.tentative_date IS NOT NULL
                               AND s.analysis_end_date > s.tentative_date THEN 1 END) AS breached_count,
                    COUNT(CASE WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL
                               AND s.tentative_date IS NOT NULL
                               AND s.analysis_end_date <= s.tentative_date THEN 1 END) AS on_time_count,
                    COUNT(CASE WHEN s.status IN ('Pending','In Progress') THEN 1 END) AS pending_count,
                    COUNT(*) AS total_count
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                WHERE s.status != 'Cancelled' {$clause}";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: [
            'completed_count' => 0,
            'avg_tat' => 0,
            'breached_count' => 0,
            'on_time_count' => 0,
            'pending_count' => 0,
            'total_count' => 0
        ];
    }

    /**
     * Get detailed TAT data per sample.
     *
     * @param array $filters  date_from, date_to, status, search
     * @return array
     */
    public function getDetailedData($filters = [])
    {
        $where = $this->buildWhereClause($filters);
        $params = $where['params'];
        $types = $where['types'];
        $clause = $where['clause'];

        $sql = "SELECT
                    s.sample_id,
                    s.sample_code,
                    s.form_number,
                    s.status,
                    s.received_date,
                    s.tentative_date,
                    s.analysis_start_date,
                    s.analysis_end_date,
                    c.client_name,
                    ci.city_name,
                    CASE
                        WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL
                        THEN DATEDIFF(s.analysis_end_date, s.received_date)
                        ELSE NULL
                    END AS tat_days,
                    CASE
                        WHEN s.status = 'Completed' AND s.analysis_end_date IS NOT NULL AND s.tentative_date IS NOT NULL
                        THEN DATEDIFF(s.analysis_end_date, s.tentative_date)
                        ELSE NULL
                    END AS delay_days,
                    CASE
                        WHEN s.status IN ('Pending','In Progress')
                        THEN DATEDIFF(CURDATE(), s.received_date)
                        ELSE NULL
                    END AS elapsed_days
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                LEFT JOIN cities ci ON s.city_id = ci.city_id
                WHERE s.status != 'Cancelled' {$clause}
                ORDER BY s.received_date DESC, s.sample_id DESC";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Get status distribution for pie chart.
     *
     * @param array $filters
     * @return array
     */
    public function getStatusDistribution($filters = [])
    {
        $where = $this->buildWhereClause($filters);
        $params = $where['params'];
        $types = $where['types'];
        $clause = $where['clause'];

        $sql = "SELECT
                    s.status,
                    COUNT(*) AS count
                FROM samples s
                INNER JOIN clients c ON s.client_id = c.client_id
                WHERE s.status != 'Cancelled' {$clause}
                GROUP BY s.status
                ORDER BY FIELD(s.status, 'Pending', 'In Progress', 'Completed')";

        $stmt = $this->conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Build a reusable WHERE clause from filters.
     *
     * @param array $filters
     * @return array  keys: clause, params, types
     */
    private function buildWhereClause($filters)
    {
        $clause = '';
        $params = [];
        $types = '';

        // Search
        $search = trim($filters['search'] ?? '');
        if ($search !== '') {
            $clause .= " AND (s.sample_code LIKE ? OR c.client_name LIKE ?)";
            $term = '%' . $search . '%';
            $params[] = $term;
            $params[] = $term;
            $types .= 'ss';
        }

        // Status filter
        $status = trim($filters['status'] ?? 'all');
        if ($status !== 'all' && $status !== '') {
            $clause .= " AND s.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        // Date range
        $dateFrom = trim($filters['date_from'] ?? '');
        $dateTo = trim($filters['date_to'] ?? '');

        if ($dateFrom !== '') {
            $clause .= " AND s.received_date >= ?";
            $params[] = $dateFrom;
            $types .= 's';
        }
        if ($dateTo !== '') {
            $clause .= " AND s.received_date <= ?";
            $params[] = $dateTo;
            $types .= 's';
        }

        // Date preset (overrides custom range)
        $preset = trim($filters['date_preset'] ?? '');
        if ($preset !== '' && $dateFrom === '' && $dateTo === '') {
            switch ($preset) {
                case 'today':
                    $clause .= " AND DATE(s.received_date) = CURDATE()";
                    break;
                case 'last7':
                    $clause .= " AND s.received_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'last30':
                    $clause .= " AND s.received_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
                case 'last90':
                    $clause .= " AND s.received_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
                    break;
            }
        }

        return ['clause' => $clause, 'params' => $params, 'types' => $types];
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
