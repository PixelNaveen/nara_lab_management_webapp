<?php
require_once __DIR__ . '/../../Config/Database.php';

class DailySummaryModel {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getDailyKPIs() {
        $today = date('Y-m-d');
        $response = [
            'intakes' => ['total' => 0, 'water' => 0, 'food' => 0, 'swab' => 0],
            'completed' => 0,
            'reports_generated' => 0,
            'revenue' => 0.00
        ];

        try {
            // 1. Intake breakdown (Water=cat1, Food=cat2, Swab=cat3 OR submission_type='swab')
            //    Use a LEFT JOIN to get the primary sample_category_id from sample_items.
            //    A sample is classified as Swab if submission_type='swab' OR the item category is 3.
            //    Water and Food are only counted for 'regular' submission_type.
            $queryIntake = "SELECT 
                              SUM(CASE 
                                    WHEN s.submission_type = 'swab' OR si.sample_category_id = 3 
                                    THEN 1 ELSE 0 
                                  END) AS swab_count,
                              SUM(CASE 
                                    WHEN s.submission_type = 'regular' AND si.sample_category_id = 1 
                                    THEN 1 ELSE 0 
                                  END) AS water_count,
                              SUM(CASE 
                                    WHEN s.submission_type = 'regular' AND si.sample_category_id = 2 
                                    THEN 1 ELSE 0 
                                  END) AS food_count,
                              COUNT(DISTINCT s.sample_id) AS total_count
                            FROM samples s
                            LEFT JOIN sample_items si 
                              ON si.sample_id = s.sample_id
                            WHERE s.received_date = ?";
            $stmt = $this->conn->prepare($queryIntake);
            $stmt->bind_param('s', $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($intakeData = $result->fetch_assoc()) {
                $response['intakes']['total'] = (int)$intakeData['total_count'];
                $response['intakes']['water'] = (int)$intakeData['water_count'];
                $response['intakes']['food']  = (int)$intakeData['food_count'];
                $response['intakes']['swab']  = (int)$intakeData['swab_count'];
            }
            $stmt->close();

            // 2. Completed Tests
            $queryCompleted = "SELECT COUNT(DISTINCT sample_id) as completed_count 
                               FROM sample_status_log 
                               WHERE new_status = 'Completed' 
                               AND DATE(updated_at) = ?";
            $stmt = $this->conn->prepare($queryCompleted);
            $stmt->bind_param('s', $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $response['completed'] = (int)$row['completed_count'];
            $stmt->close();

            // 3. Reports Generated
            $queryReports = "SELECT COUNT(*) as report_count 
                             FROM final_test_reports 
                             WHERE DATE(created_at) = ?";
            $stmt = $this->conn->prepare($queryReports);
            $stmt->bind_param('s', $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $response['reports_generated'] = (int)$row['report_count'];
            $stmt->close();

            // 4. Revenue
            $queryRevenue = "SELECT SUM(grand_total) as total_revenue 
                             FROM samples 
                             WHERE payment_status = 'Paid' 
                             AND DATE(payment_date) = ?";
            $stmt = $this->conn->prepare($queryRevenue);
            $stmt->bind_param('s', $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $response['revenue'] = $row['total_revenue'] ? (float)$row['total_revenue'] : 0.00;
            $stmt->close();

            return $response;
        } catch (Exception $e) {
            error_log("DailySummaryModel::getDailyKPIs Error: " . $e->getMessage());
            return $response;
        }
    }

    public function getIntakeTrend($dateFrom = null, $dateTo = null) {
        $trend = [];
        try {
            // Use provided date range, or default to last 7 days
            if ($dateFrom && $dateTo) {
                $query = "SELECT received_date, COUNT(*) as daily_count 
                          FROM samples 
                          WHERE DATE(received_date) BETWEEN ? AND ?
                          AND status != 'Cancelled'
                          GROUP BY received_date 
                          ORDER BY received_date ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param('ss', $dateFrom, $dateTo);
            } else {
                $query = "SELECT received_date, COUNT(*) as daily_count 
                          FROM samples 
                          WHERE received_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                          AND status != 'Cancelled'
                          GROUP BY received_date 
                          ORDER BY received_date ASC";
                $stmt = $this->conn->prepare($query);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $results = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Fill gaps with 0 for every day in the range
            $startDate = $dateFrom ? new DateTime($dateFrom) : (new DateTime())->modify('-6 days');
            $endDate = $dateTo ? new DateTime($dateTo) : new DateTime();
            
            $current = clone $startDate;
            while ($current <= $endDate) {
                $trend[$current->format('Y-m-d')] = 0;
                $current->modify('+1 day');
            }

            foreach ($results as $row) {
                if (isset($trend[$row['received_date']])) {
                    $trend[$row['received_date']] = (int)$row['daily_count'];
                }
            }
            
            return [
                'labels' => array_keys($trend),
                'data' => array_values($trend)
            ];
        } catch (Exception $e) {
            error_log("DailySummaryModel::getIntakeTrend Error: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    public function getRecentIntakes() {
        $today = date('Y-m-d');
        try {
            $query = "SELECT s.sample_code, c.client_name, s.received_time, s.status, s.payment_status
                      FROM samples s
                      JOIN clients c ON s.client_id = c.client_id
                      WHERE s.received_date = ?
                      ORDER BY s.sample_id DESC 
                      LIMIT 10";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('s', $today);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $data;
        } catch (Exception $e) {
            error_log("DailySummaryModel::getRecentIntakes Error: " . $e->getMessage());
            return [];
        }
    }
}
?>
