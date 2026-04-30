<?php
// src/Models/CityModel.php - FINAL COMPLETE VERSION

require_once __DIR__ . '/../../Config/Database.php';

class CityModel
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Get all cities with filters
     */
    public function getAllCities($filters = [])
    {
        try {
            $sql = "SELECT city_id, city_name, is_predefined, needs_review, 
                           usage_count, is_active, created_at, created_by 
                    FROM cities 
                    WHERE is_deleted = 0";

            // Apply filters
            if (isset($filters['is_active']) && $filters['is_active'] !== '') {
                $sql .= " AND is_active = " . intval($filters['is_active']);
            }

            if (isset($filters['type']) && $filters['type'] !== 'all') {
                if ($filters['type'] === 'predefined') {
                    $sql .= " AND is_predefined = 1";
                } elseif ($filters['type'] === 'user-added') {
                    $sql .= " AND created_by != 'system'";
                }
            }

            // Search matches from FIRST letter only
            if (isset($filters['search']) && trim($filters['search']) !== '') {
                $search = $this->conn->real_escape_string($filters['search']);
                $sql .= " AND LOWER(city_name) LIKE LOWER('{$search}%')";
            }

            // Sorting
            $sortBy = $filters['sort'] ?? 'usage';
            switch ($sortBy) {
                case 'name':
                    $sql .= " ORDER BY city_name ASC";
                    break;
                case 'date':
                    $sql .= " ORDER BY created_at DESC";
                    break;
                default:
                    $sql .= " ORDER BY city_name ASC";
            }

            $result = $this->conn->query($sql);
            $cities = [];

            while ($row = $result->fetch_assoc()) {
                $cities[] = $row;
            }

            return [
                'data' => $cities,
                'total' => count($cities)
            ];
        } catch (Exception $e) {
            error_log("CityModel::getAllCities Error: " . $e->getMessage());
            return ['data' => [], 'total' => 0];
        }
    }

    /**
     * Get city by ID
     */
    public function getCityById($cityId)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT * FROM cities WHERE city_id = ? AND is_deleted = 0"
            );
            $stmt->bind_param("i", $cityId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("CityModel::getCityById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if city exists (case-insensitive)
     */
    public function cityExists($cityName, $excludeId = null)
    {
        try {
            $sql = "SELECT city_id FROM cities 
                    WHERE LOWER(city_name) = LOWER(?) 
                      AND is_deleted = 0";

            if ($excludeId) {
                $sql .= " AND city_id != ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("si", $cityName, $excludeId);
            } else {
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("s", $cityName);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            return $result->num_rows > 0;
        } catch (Exception $e) {
            error_log("CityModel::cityExists Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Insert new city
     */
    public function insertCity($cityName, $isPredefined, $createdBy = 'admin')
    {
        try {
            // Sanitize city name
            $cityName = $this->sanitizeCityName($cityName);

            $stmt = $this->conn->prepare(
                "INSERT INTO cities (city_name, is_predefined, created_by, usage_count) 
                 VALUES (?, ?, ?, 0)"
            );

            $stmt->bind_param("sis", $cityName, $isPredefined, $createdBy);

            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
            return false;
        } catch (Exception $e) {
            error_log("CityModel::insertCity Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update city
     */
    public function updateCity($cityId, $cityName, $isPredefined)
    {
        try {
            $cityName = $this->sanitizeCityName($cityName);

            $stmt = $this->conn->prepare(
                "UPDATE cities 
                 SET city_name = ?, is_predefined = ? 
                 WHERE city_id = ?"
            );

            $stmt->bind_param("sii", $cityName, $isPredefined, $cityId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CityModel::updateCity Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Soft delete city
     */
    public function softDeleteCity($cityId)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE cities SET is_deleted = 1, is_active = 0 WHERE city_id = ?"
            );
            $stmt->bind_param("i", $cityId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CityModel::softDeleteCity Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle city status
     */
    public function toggleStatus($cityId, $isActive)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE cities SET is_active = ? WHERE city_id = ?"
            );
            $stmt->bind_param("ii", $isActive, $cityId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CityModel::toggleStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get statistics - UPDATED
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total' => 0,
                'predefined' => 0,
                'user_added' => 0
            ];

            // Count cities where created_by != 'system' for user-added
            $result = $this->conn->query(
                "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_predefined = 1 THEN 1 ELSE 0 END) as predefined,
                    SUM(CASE WHEN created_by != 'system' THEN 1 ELSE 0 END) as user_added
                 FROM cities 
                 WHERE is_deleted = 0"
            );

            if ($row = $result->fetch_assoc()) {
                $stats = $row;
            }

            return $stats;
        } catch (Exception $e) {
            error_log("CityModel::getStatistics Error: " . $e->getMessage());
            return ['total' => 0, 'predefined' => 0, 'user_added' => 0];
        }
    }

    /**
     * Sanitize city name
     */
    private function sanitizeCityName($cityName)
    {
        // Remove extra spaces
        $cityName = trim($cityName);
        $cityName = preg_replace('/\s+/', ' ', $cityName);

        // Capitalize first letter of each word
        $cityName = ucwords(strtolower($cityName));

        // Remove special characters (keep letters, numbers, spaces, hyphens)
        $cityName = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $cityName);

        return $cityName;
    }

    /**
     * Check if deleted record exists
     */
    public function findDeletedByName($cityName)
    {
        try {
            $stmt = $this->conn->prepare(
                "SELECT city_id FROM cities 
                 WHERE LOWER(city_name) = LOWER(?) AND is_deleted = 1"
            );
            $stmt->bind_param("s", $cityName);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Reactivate deleted city
     */
    public function reactivateCity($cityId)
    {
        try {
            $stmt = $this->conn->prepare(
                "UPDATE cities SET is_deleted = 0, is_active = 1 WHERE city_id = ?"
            );
            $stmt->bind_param("i", $cityId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("CityModel::reactivateCity Error: " . $e->getMessage());
            return false;
        }
    }
}
