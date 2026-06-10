<?php
/**
 * Binalbagan Catholic College Official Schedule Management System
 * Central Environment Config
 */

// Database configuration
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'bcc_schedule');
define('DB_USER', 'root');
define('DB_PASS', '');

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Get PDO Database Connection
 */
function getDBConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Connection failed. Please contact the administrator.");
    }
}

/**
 * Authentication check
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']);
}

/**
 * Securely redirect
 */
function redirect($path) {
    header("Location: $path");
    exit();
}

/**
 * CSRF Protection
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

/**
 * Sanitize Output
 */
function h($string) {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}
?>
