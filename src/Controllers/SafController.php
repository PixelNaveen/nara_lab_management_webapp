<?php

/**
 * SAF Controller - Sample Acceptance Form Generator
 * Version: 2.1 - CORRECTED
 * 
 * FIXED: UTF-8 encoding (proper emojis)
 * Routes SAF requests and renders forms
 */

session_start();

require_once __DIR__ . '/../Models/SAFModel.php';
require_once __DIR__ . '/../Helpers/Functions.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Unauthorized</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                padding: 60px; 
                text-align: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .error-box {
                background: white;
                color: #333;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 500px;
                margin: 0 auto;
            }
            h2 { color: #dc3545; margin-bottom: 20px; }
            a { 
                display: inline-block;
                margin-top: 20px;
                padding: 12px 24px;
                background: #007bff;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                transition: background 0.3s;
            }
            a:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>⛔ Unauthorized Access</h2>
            <p>You must be logged in to view this form.</p>
            <a href="/index.php">🔐 Go to Login</a>
        </div>
    </body>
    </html>';
    exit;
}

// Get action
$action = $_GET['action'] ?? 'view';

// Initialize model
try {
    $safModel = new SAFModel();
} catch (Exception $e) {
    logError($e->getMessage(), 'SAFController initialization');
    http_response_code(500);
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Database Error</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                padding: 60px; 
                text-align: center;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .error-box {
                background: white;
                color: #333;
                padding: 40px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                max-width: 500px;
                margin: 0 auto;
            }
            h2 { color: #ffc107; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h2>⚠️ Database Connection Error</h2>
            <p>Unable to connect to database. Please try again later.</p>
            <p><small>Error: ' . htmlspecialchars($e->getMessage()) . '</small></p>
        </div>
    </body>
    </html>';
    exit;
}

// Route actions
switch ($action) {
    case 'view':
        handleViewSAF($safModel);
        break;

    case 'checkExists':
        handleCheckExists($safModel);
        break;

    default:
        http_response_code(400);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Invalid Action</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 60px; 
                    text-align: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                .error-box {
                    background: white;
                    color: #333;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    max-width: 500px;
                    margin: 0 auto;
                }
                h2 { color: #dc3545; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>❌ Invalid Action</h2>
                <p>The requested action is not valid.</p>
            </div>
        </body>
        </html>';
}

/**
 * Handle view SAF request
 */
function handleViewSAF($model)
{
    $sampleId = intval($_GET['sample_id'] ?? 0);

    if ($sampleId === 0) {
        http_response_code(400);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Invalid Request</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 60px; 
                    text-align: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                .error-box {
                    background: white;
                    color: #333;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    max-width: 500px;
                    margin: 0 auto;
                }
                h2 { color: #dc3545; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>❌ Error: Sample ID Required</h2>
                <p>Please provide a valid sample ID.</p>
            </div>
        </body>
        </html>';
        exit;
    }

    // Fetch SAF data
    $result = $model->getSAFData($sampleId);

    if (!$result['success']) {
        http_response_code(404);
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Sample Not Found</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    padding: 60px; 
                    text-align: center;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                .error-box {
                    background: white;
                    color: #333;
                    padding: 40px;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                    max-width: 500px;
                    margin: 0 auto;
                }
                h2 { color: #dc3545; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="error-box">
                <h2>❌ Error: ' . htmlspecialchars($result['message']) . '</h2>
                <p>Sample not found or data unavailable.</p>
                <p><small>Sample ID: ' . $sampleId . '</small></p>
            </div>
        </body>
        </html>';
        exit;
    }

    // Extract data for template
    $data = $result['data'];

    // Set content type
    header('Content-Type: text/html; charset=utf-8');

    // Load template with data
    include __DIR__ . '/../Includes/saf-template.php';
}

/**
 * Handle check if SAF exists (AJAX endpoint)
 */
function handleCheckExists($model)
{
    header('Content-Type: application/json; charset=utf-8');

    $sampleId = intval($_GET['sample_id'] ?? 0);

    if ($sampleId === 0) {
        echo json_encode([
            'exists' => false,
            'message' => 'Invalid sample ID'
        ]);
        exit;
    }

    $exists = $model->safExists($sampleId);

    echo json_encode([
        'exists' => $exists,
        'sample_id' => $sampleId
    ]);
}
