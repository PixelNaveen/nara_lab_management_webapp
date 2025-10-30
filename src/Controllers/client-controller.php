<?php

require_once "Config/Database.php";
require_once "src/Models/client-model.php";
$db = new Database();  // Assuming Database class connects in __construct()
$model = new Client_model($db);

header("Content-Type: application/json");

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case "create":
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                "clientName" => $_POST["clientName"] ?? '',
                "address" => $_POST["address"] ?? '',
                "city" => $_POST["city"] ?? '',
                "phoneNo" => $_POST["phoneNo"] ?? '',
                "contactPerson" => $_POST["contactPerson"] ?? ''
            ];
            $id = $model->insert($data);
            echo json_encode(["status" => ($id > 0) ? "success" : "error"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid method"]);
        }
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
        break;
}
