<?php
require_once __DIR__ . '/../Includes/session-helper.php';
checkSessionTimeout(true);
require_once __DIR__ . '/../Models/SwabModel.php';
header('Content-Type: application/json');

// Custom error handler to return JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// ========== RATE LIMITING (simple session-based) ==========
$rateLimitKey = 'swab_rate_limit';
$rateLimitMax = 60; // max requests per window
$rateLimitWindow = 60; // seconds

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'start' => time()];
}

$rl = &$_SESSION[$rateLimitKey];
if (time() - $rl['start'] > $rateLimitWindow) {
    $rl = ['count' => 0, 'start' => time()];
}
$rl['count']++;

if ($rl['count'] > $rateLimitMax) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many requests. Please wait and try again.']);
    exit;
}

// ========== CSRF & AUTH ==========
$stateChangingActions = ['insert', 'update', 'delete', 'saveCombo', 'deleteCombo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Auth & Permission Check for state-changing actions
    if (in_array($action, $stateChangingActions)) {
        if (!isset($_SESSION['user_id']) || !in_array(strtoupper($_SESSION['role'] ?? ''), ['ADMIN', 'LABMANAGER'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Only Admin or Manager can modify swab pricing.']);
            exit;
        }

        // CSRF Check
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid security token']);
            exit;
        }
    }
}

$model = new SwabModel();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ========== FETCH ALL (Individual + Combo merged) ==========
        case 'fetchAll':
            $filters = [];
            if (isset($_POST['is_active']) && $_POST['is_active'] !== '') {
                $filters['is_active'] = intval($_POST['is_active']);
            }
            if (isset($_POST['search']) && trim($_POST['search']) !== '') {
                $filters['search'] = trim($_POST['search']);
            }

            // Fetch individual swab params
            $individuals = $model->getAllSwabParams($filters);
            // Add type indicator to each individual
            foreach ($individuals as &$item) {
                $item['type'] = 'individual';
                $item['id'] = $item['swab_param_id'];
            }
            unset($item);

            // Fetch combo swab params
            $combos = $model->getAllSwabCombos($filters);
            // Add id alias for combos
            foreach ($combos as &$comboItem) {
                $comboItem['id'] = $comboItem['combo_id'];
            }
            unset($comboItem);

            // Merge both arrays
            $merged = array_merge($individuals, $combos);

            echo json_encode(['status' => 'success', 'data' => $merged]);
            break;

        // ========== GET PARAMETERS DROPDOWN (Individual - excludes existing) ==========
        case 'fetchDropdown':
            $params = $model->getParametersDropdown();
            echo json_encode(['status' => 'success', 'data' => $params]);
            break;

        // ========== GET ALL SWAB-ENABLED PARAMS (Combo dropdown - includes all) ==========
        case 'fetchComboDropdown':
            $params = $model->getAllSwabEnabledParams();
            echo json_encode(['status' => 'success', 'data' => $params]);
            break;

        // ========== GET INDIVIDUAL SWAB BY ID ==========
        case 'getById':
            $id = intval($_POST['swab_param_id'] ?? 0);
            if ($id <= 0) throw new Exception('Invalid ID');

            $row = $model->getSwabById($id);
            if ($row) {
                echo json_encode(['status' => 'success', 'data' => $row]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Not found']);
            }
            break;

        // ========== GET COMBO BY ID ==========
        case 'getComboById':
            $comboId = intval($_POST['combo_id'] ?? 0);
            if ($comboId <= 0) throw new Exception('Invalid combo ID');

            $combo = $model->getSwabComboById($comboId);
            if ($combo) {
                echo json_encode(['status' => 'success', 'data' => $combo]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Combo not found']);
            }
            break;

        // ========== INSERT NEW INDIVIDUAL SWAB PARAM ==========
        case 'insert':
            $paramId = intval($_POST['param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($paramId <= 0) throw new Exception('Parameter is required');
            if ($price < 0) throw new Exception('Price cannot be negative');

            // Check if already exists
            $existing = $model->findByParamId($paramId);
            if ($existing) {
                if (intval($existing['is_deleted']) === 1) {
                    if ($model->reactivateSwabByParam($paramId, $price, $isActive)) {
                        echo json_encode(['status' => 'success', 'message' => 'Swab parameter restored successfully']);
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to restore swab parameter']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Swab parameter already exists for this parameter']);
                }
                exit;
            }

            if ($model->insertSwab($paramId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Insert failed']);
            }
            break;

        // ========== UPDATE INDIVIDUAL PRICE ==========
        case 'update':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            if ($swabId <= 0) throw new Exception('Invalid ID');
            if ($price < 0) throw new Exception('Price cannot be negative');

            $current = $model->getSwabById($swabId);
            if (
                $current &&
                abs(floatval($current['swab_price']) - $price) < 0.001 &&
                intval($current['is_active']) === $isActive
            ) {
                echo json_encode(['status' => 'info', 'message' => 'No update detected.']);
                exit;
            }

            if ($model->updateSwabPrice($swabId, $price, $isActive)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab price updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Update failed']);
            }
            break;

        // ========== DELETE INDIVIDUAL SWAB PARAM ==========
        case 'delete':
            $swabId = intval($_POST['swab_param_id'] ?? 0);
            if ($swabId <= 0) throw new Exception('Invalid ID');

            if ($model->softDeleteById($swabId)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab parameter deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Delete failed']);
            }
            break;

        // ========== SAVE COMBO (Insert or Update) ==========
        case 'saveCombo':
            $comboId = intval($_POST['combo_id'] ?? 0);
            $price = isset($_POST['price']) && $_POST['price'] !== '' ? floatval($_POST['price']) : 0.00;
            $isActive = (isset($_POST['is_active']) && ($_POST['is_active'] === '1' || $_POST['is_active'] == 1)) ? 1 : 0;

            // Parse param_ids - can come as comma-separated string or array
            $rawParamIds = $_POST['param_ids'] ?? '';
            if (is_string($rawParamIds)) {
                $paramIds = array_filter(array_map('intval', explode(',', $rawParamIds)));
            } else if (is_array($rawParamIds)) {
                $paramIds = array_filter(array_map('intval', $rawParamIds));
            } else {
                $paramIds = [];
            }

            // Validation
            if (count($paramIds) < 2) {
                throw new Exception('A combo requires at least 2 parameters');
            }
            if ($price < 0) {
                throw new Exception('Price cannot be negative');
            }

            // Check for duplicate combo
            $excludeId = $comboId > 0 ? $comboId : null;
            if ($model->hasExactCombo($paramIds, $excludeId)) {
                echo json_encode(['status' => 'error', 'message' => 'This exact parameter combination already exists']);
                exit;
            }

            if ($comboId > 0) {
                // UPDATE existing combo
                $result = $model->updateSwabCombo($comboId, $paramIds, $price, $isActive);
                if ($result) {
                    $comboName = $model->generateComboName($paramIds);
                    echo json_encode(['status' => 'success', 'message' => "Combo \"$comboName\" updated successfully"]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to update combo']);
                }
            } else {
                // INSERT new combo
                $newId = $model->insertSwabCombo($paramIds, $price, $isActive);
                if ($newId) {
                    $comboName = $model->generateComboName($paramIds);
                    echo json_encode(['status' => 'success', 'message' => "Combo \"$comboName\" created successfully"]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to create combo']);
                }
            }
            break;

        // ========== DELETE COMBO ==========
        case 'deleteCombo':
            $comboId = intval($_POST['combo_id'] ?? 0);
            if ($comboId <= 0) throw new Exception('Invalid combo ID');

            if ($model->softDeleteComboById($comboId)) {
                echo json_encode(['status' => 'success', 'message' => 'Swab combo deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to delete combo']);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Swab Controller Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

