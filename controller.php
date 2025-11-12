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

    // =================== GET ALL PARAMETERS WITH PAGINATION ===================
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
                    COUNT(DISTINCT pv.variant_id) as variant_count
                FROM test_parameters tp
                LEFT JOIN parameter_variants pv ON tp.parameter_id = pv.parameter_id 
                    AND pv.is_active = 1 AND pv.is_deleted = 0
                WHERE tp.is_deleted = 0";

        $params = [];
        $types = "";

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND tp.is_active = ?";
            $params[] = intval($filters['is_active']);
            $types .= "i";
        }

        if (isset($filters['search']) && $filters['search'] !== '') {
            $sql .= " AND (tp.parameter_name LIKE ? OR tp.parameter_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        $sql .= " GROUP BY tp.parameter_id ORDER BY tp.parameter_id ASC";

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as counted";
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
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $parameters = [];
        while ($row = $result->fetch_assoc()) {
            $parameters[] = $row;
        }

        return [
            'data' => $parameters,
            'total' => $total
        ];
    }

    // =================== GET PARAMETER BY ID ===================
    public function getParameterById($id)
    {
        // UPDATED: Added method_id to SELECT
        $stmt = $this->conn->prepare(
            "SELECT parameter_id, parameter_code, parameter_name, 
                    parameter_category, base_unit, has_variants, 
                    swab_enabled, is_active, method_id
            FROM test_parameters 
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== DUPLICATE CHECK ===================
    public function isDuplicate($name, $excludeId = null)
    {
        $sql = "SELECT parameter_id FROM test_parameters 
                WHERE parameter_name = ? AND is_deleted = 0";

        $params = [$name];
        $types = "s";

        if ($excludeId) {
            $sql .= " AND parameter_id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // =================== FIND DELETED RECORD ===================
    public function findDeletedByName($name)
    {
        $stmt = $this->conn->prepare(
            "SELECT parameter_id, parameter_code 
            FROM test_parameters 
            WHERE parameter_name = ? AND is_deleted = 1 
            LIMIT 1"
        );
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // =================== GET NEXT CODE ===================
    public function getNextParameterCode()
    {
        $result = $this->conn->query(
            "SELECT parameter_code FROM test_parameters 
            ORDER BY parameter_id DESC LIMIT 1"
        );

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastCode = strtoupper($row['parameter_code']);

            if (strlen($lastCode) === 1 && ctype_alpha($lastCode)) {
                $nextCode = chr(ord($lastCode) + 1);
                return $nextCode > 'Z' ? 'AA' : $nextCode;
            }

            if (ctype_alpha($lastCode)) {
                return ++$lastCode;
            }
        }

        return 'A';
    }

    // =================== INSERT PARAMETER ===================
    // UPDATED: Added optional $methodId parameter (defaults to null)
    public function insertParameter($name, $category, $baseUnit, $swabEnabled, $isActive = 1, $methodId = null)
    {
        $code = $this->getNextParameterCode();

        // UPDATED: Added method_id to INSERT fields and VALUES
        $stmt = $this->conn->prepare(
            "INSERT INTO test_parameters 
            (parameter_code, parameter_name, parameter_category, base_unit, 
             swab_enabled, has_variants, is_active, is_deleted, method_id, created_at)
            VALUES (?, ?, ?, ?, ?, 0, ?, 0, ?, NOW())"
        );

        // UPDATED: Adjusted bind_param to include method_id (type 'i', value can be null)
        $stmt->bind_param("ssssiii", $code, $name, $category, $baseUnit, $swabEnabled, $isActive, $methodId);

        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    // =================== REACTIVATE PARAMETER ===================
    // UPDATED: Added optional $methodId parameter (defaults to null)
    public function reactivateParameter($id, $category, $baseUnit, $swabEnabled, $isActive, $methodId = null)
    {
        // UPDATED: Added method_id to SET clause
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET parameter_category = ?, 
                base_unit = ?, 
                swab_enabled = ?,
                is_active = ?,
                method_id = ?,
                is_deleted = 0,
                updated_at = NOW()
            WHERE parameter_id = ?"
        );

        // UPDATED: Adjusted bind_param to include method_id (type 'i', value can be null)
        $stmt->bind_param("ssiiii", $category, $baseUnit, $swabEnabled, $isActive, $methodId, $id);
        return $stmt->execute();
    }

    // =================== UPDATE PARAMETER ===================
    // UPDATED: Added $methodId parameter before $id
    public function updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive, $methodId = null)
    {
        // UPDATED: Added method_id to SET clause
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET parameter_code = ?,
                parameter_name = ?, 
                parameter_category = ?,
                base_unit = ?, 
                swab_enabled = ?,
                is_active = ?,
                method_id = ?,
                updated_at = NOW()
            WHERE parameter_id = ? AND is_deleted = 0"
        );

        // UPDATED: Adjusted bind_param to include method_id (type 'i', value can be null) before id
        $stmt->bind_param("ssssiiii", $code, $name, $category, $baseUnit, $swabEnabled, $isActive, $methodId, $id);
        return $stmt->execute();
    }

    // =================== SOFT DELETE ===================
    public function softDeleteParameter($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET is_deleted = 1, updated_at = NOW() 
            WHERE parameter_id = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // =================== TOGGLE STATUS ===================
    public function toggleStatus($id, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE test_parameters 
            SET is_active = ?, updated_at = NOW() 
            WHERE parameter_id = ? AND is_deleted = 0"
        );
        $stmt->bind_param("ii", $isActive, $id);
        return $stmt->execute();
    }

    // =================== CHECK VARIANTS ===================
    public function hasActiveVariants($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as count FROM parameter_variants 
            WHERE parameter_id = ? AND is_active = 1 AND is_deleted = 0"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }

    // =================== SWAB PRICE MANAGEMENT ===================
    public function createInitialSwabPrice($paramId, $price = 0.00)
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO swab_param (param_id, swab_price, is_active, is_deleted, created_at)
            VALUES (?, ?, 1, 0, NOW())"
        );
        $stmt->bind_param("id", $paramId, $price);
        return $stmt->execute();
    }

    public function reactivateSwabPrice($paramId, $price = 0.00)
    {
        $stmt = $this->conn->prepare(
            "SELECT swab_param_id FROM swab_param 
            WHERE param_id = ? AND is_deleted = 1 
            LIMIT 1"
        );
        $stmt->bind_param("i", $paramId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt2 = $this->conn->prepare(
                "UPDATE swab_param 
                SET swab_price = ?, is_active = 1, is_deleted = 0, updated_at = NOW()
                WHERE swab_param_id = ?"
            );
            $stmt2->bind_param("di", $price, $row['swab_param_id']);
            return $stmt2->execute();
        }

        return $this->createInitialSwabPrice($paramId, $price);
    }

    public function disableSwabParam($paramId)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param 
            SET is_deleted = 1, updated_at = NOW()
            WHERE param_id = ?"
        );
        $stmt->bind_param("i", $paramId);
        return $stmt->execute(); 
    }

    public function syncSwabParamStatus($paramId, $isActive)
    {
        $stmt = $this->conn->prepare(
            "UPDATE swab_param 
            SET is_active = ?, updated_at = NOW()
            WHERE param_id = ? AND is_deleted = 0"
        );   
        $stmt->bind_param("ii", $isActive, $paramId);
        return $stmt->execute();
    }

    public function getActiveMethods()
{
    $sql = "SELECT method_id, method_name 
            FROM test_methods
            WHERE is_active = 1 AND is_deleted = 0
            ORDER BY method_id ASC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $methods = [];
    while ($row = $result->fetch_assoc()) {
        $methods[] = $row;
    }
    return $methods;
}

// =================== GET METHOD ID BY NAME ===================
public function getMethodIdByName($methodName)
{
    $stmt = $this->conn->prepare(
        "SELECT method_id FROM test_methods 
         WHERE method_name = ? AND is_deleted = 0 LIMIT 1"
    );
    $stmt->bind_param("s", $methodName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? intval($row['method_id']) : false;
}


}
?><?php
session_start();

require_once __DIR__ . '/../Models/parameter-model.php';
header('Content-Type: application/json');

// CSRF validation for state-changing operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!in_array($action, ['fetchAll', 'getById','fetchMethods'])) {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new ParameterModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        // ========== FETCH ALL ==========
        case 'fetchAll':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }

            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 50;
            $filters['page'] = $page;
            $filters['limit'] = $limit;

            $result = $model->getAllParameters($filters);
            echo json_encode([
                'status' => 'success',
                'data' => $result['data'],
                'total' => $result['total'],
                'page' => $page,
                'totalPages' => ceil($result['total'] / $limit)
            ]);
            break;

        // ========== GET BY ID ==========
        case 'getById':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            $parameter = $model->getParameterById($id);
            if ($parameter) {
                // UPDATED: Fetch method_ids instead of single method_id
                $parameter['method_ids'] = $model->getParameterMethodIds($id);
                echo json_encode(['status' => 'success', 'data' => $parameter]);
            } else {
                throw new Exception('Parameter not found');
            }
            break;

        // ========== INSERT ==========
        case 'insert':
            $name = trim($_POST['parameter_name'] ?? '');
            $category = trim($_POST['parameter_category'] ?? '');
            $baseUnit = trim($_POST['base_unit'] ?? '');
            $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
            $swabPrice = isset($_POST['swab_price']) && $_POST['swab_price'] !== ''
                ? floatval($_POST['swab_price']) : 0.00;
            $isActive = isset($_POST['is_active']) ? intval($_POST['is_active']) : 1;
            // NEW: Handle array of method_ids
            $methodIds = isset($_POST['method_ids']) && is_array($_POST['method_ids'])
                ? array_filter(array_map('intval', $_POST['method_ids']))
                : [];

            if ($name === '') {
                throw new Exception('Parameter name is required');
            }

            // Check deleted record first
            $deletedRecord = $model->findDeletedByName($name);

            if ($deletedRecord) {
                // Reactivate
                // UPDATED: Removed $methodId, pass array to sync methods separately
                $result = $model->reactivateParameter(
                    $deletedRecord['parameter_id'],
                    $category,
                    $baseUnit,
                    $swabEnabled,
                    $isActive
                );

                if ($result) {
                    $paramId = $deletedRecord['parameter_id'];

                    // NEW: Sync methods on reactivation
                    $model->syncParameterMethods($paramId, $methodIds);

                    // Handle swab price on reactivation
                    if ($swabEnabled == 1) {
                        $model->reactivateSwabPrice($paramId, $swabPrice);
                    }

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Parameter reactivated successfully',
                        'parameter_id' => $paramId
                    ]);
                } else {
                    throw new Exception('Failed to reactivate parameter');
                }
            } else {
                // Check active duplicate
                if ($model->isDuplicate($name)) {
                    throw new Exception('Parameter with this name already exists');
                }

                // Insert new
                // UPDATED: Removed $methodId from insertParameter
                $paramId = $model->insertParameter($name, $category, $baseUnit, $swabEnabled, $isActive);

                if ($paramId) {
                    // NEW: Assign methods
                    $model->assignMethodsToParameter($paramId, $methodIds);

                    // Create initial swab price if enabled
                    if ($swabEnabled == 1) {
                        $model->createInitialSwabPrice($paramId, $swabPrice);
                    }

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Parameter added successfully',
                        'parameter_id' => $paramId
                    ]);
                } else {
                    throw new Exception('Failed to insert parameter');
                }
            }
            break;

        // ========== UPDATE ==========
       // ========== UPDATE ==========
case 'update':
    $id = intval($_POST['parameter_id'] ?? 0);
    $code = trim($_POST['parameter_code'] ?? '');
    $name = trim($_POST['parameter_name'] ?? '');
    $category = trim($_POST['parameter_category'] ?? '');
    $baseUnit = trim($_POST['base_unit'] ?? '');
    $swabEnabled = intval($_POST['swab_enabled'] ?? 0);
    $isActive = intval($_POST['is_active'] ?? 1);
    $methodIds = isset($_POST['method_ids']) && is_array($_POST['method_ids'])
        ? array_filter(array_map('intval', $_POST['method_ids']))
        : [];

    if ($id <= 0) {
        throw new Exception('Invalid parameter ID');
    }
    if ($name === '') {
        throw new Exception('Parameter name is required');
    }

    // Get current parameter and methods
    $currentParam = $model->getParameterById($id);
    $currentMethods = $model->getParameterMethodIds($id);

    // Sort arrays for comparison
    sort($currentMethods);
    sort($methodIds);

    // Check if anything changed
    $fieldsUnchanged =
        $currentParam['parameter_name'] === $name &&
        $currentParam['parameter_category'] === $category &&
        $currentParam['base_unit'] === $baseUnit &&
        intval($currentParam['swab_enabled']) === $swabEnabled &&
        intval($currentParam['is_active']) === $isActive &&
        $currentMethods === $methodIds;

    if ($fieldsUnchanged) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No changes detected'
        ]);
        exit;
    }

    // Check for duplicate name + same methods
    $allParams = $model->getAllParameters(['is_active'=>'']);
    foreach ($allParams['data'] as $param) {
        if ($param['parameter_id'] == $id) continue;

        if ($param['parameter_name'] === $name) {
            $existingMethodIds = $model->getParameterMethodIds($param['parameter_id']);
            sort($existingMethodIds);
            if ($existingMethodIds === $methodIds) {
                throw new Exception('Another parameter with same name and same methods already exists');
            }
        }
    }

    // Get previous swab status
    $wasSwabEnabled = $currentParam['swab_enabled'];

    // Perform update
    if ($model->updateParameter($id, $code, $name, $category, $baseUnit, $swabEnabled, $isActive)) {
        // Sync methods
        $model->syncParameterMethods($id, $methodIds);

        // Handle swab changes
        if ($swabEnabled == 1 && $wasSwabEnabled == 0) {
            $model->createInitialSwabPrice($id, 0.00);
        } elseif ($swabEnabled == 0 && $wasSwabEnabled == 1) {
            $model->disableSwabParam($id);
        }

        // Sync swab active status
        if ($swabEnabled == 1) {
            $model->syncSwabParamStatus($id, $isActive);
        }

        echo json_encode([
            'status' => 'success',
            'message' => 'Parameter updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update parameter');
    }
    break;


        // ========== DELETE ==========
        case 'delete':
            $id = intval($_POST['parameter_id'] ?? 0);
            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            if ($model->hasActiveVariants($id)) {
                echo json_encode([
                    'status' => 'warning',
                    'message' => 'This parameter has active variants. Please deactivate them first.'
                ]);
                exit;
            }

            if ($model->softDeleteParameter($id)) {
                $model->disableSwabParam($id);

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Parameter deleted successfully'
                ]);
            } else {
                throw new Exception('Failed to delete parameter');
            }
            break;

        // ========== TOGGLE STATUS ==========
        case 'toggleStatus':
            $id = intval($_POST['parameter_id'] ?? 0);
            $isActive = intval($_POST['is_active'] ?? 1);

            if ($id <= 0) {
                throw new Exception('Invalid parameter ID');
            }

            if ($model->toggleStatus($id, $isActive)) {
                $model->syncSwabParamStatus($id, $isActive);

                echo json_encode([
                    'status' => 'success',
                    'message' => $isActive ? 'Parameter activated' : 'Parameter deactivated'
                ]);
            } else {
                throw new Exception('Failed to update status');
            }
            break;

        case 'fetchMethods':
            $methods = $model->getActiveMethods();
            echo json_encode([
                'status' => 'success',
                'data' => $methods
            ]);
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Parameter Controller Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>