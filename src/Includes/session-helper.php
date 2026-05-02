<?php

/**
 * Session Activity Tracker
 * Use this in AJAX controllers to maintain sliding timeout
 */

//  CONFIGURE SESSION LIFETIME (Override server defaults)
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 0);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function checkSessionTimeout($isAjax = true)
{
    $sessionTimeout = 2 * 60 * 60; // 2 hours in seconds

    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $sessionTimeout) {
            // Session expired
            $_SESSION = [];
            session_destroy();

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Session expired due to inactivity. Please log in again.',
                    'timeout' => true
                ]);
                exit;
            } else {
                header('Location: src/Views/login.php?timeout=1');
                exit;
            }
        }
    }

    // If we reach here, session is valid or not yet set
    if (isset($_SESSION['user_id'])) {
        $_SESSION['last_activity'] = time();
    }
}
