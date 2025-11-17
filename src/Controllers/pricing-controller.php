<?php 
session_start();

require_once __DIR__ . '/../Models/pricing-model.php';
header('Content-Type: application/json');

// CSRF Validation for state-changing operations
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $action = $_POST['action'] ?? '';
    $readOnlyActions = ['fetchAllIndividuals', 'fetchAllCombos', 'getIndividualById', 'getComboById', 'fetchActiveParameters','previewComboName'];

    if(!in_array($action, $readOnlyActions)){
        if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']){
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }
    }
}