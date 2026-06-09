<?php
require_once __DIR__ . '/db_config.php';

function getDb() {
    $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
    $user = defined('DB_USER') ? DB_USER : 'root';
    $pass = defined('DB_PASS') ? DB_PASS : '';
    $name = defined('DB_NAME') ? DB_NAME : 'farmer_loan';

    $db = new mysqli($host, $user, $pass, $name);
    if ($db->connect_error) {
        die('Database connection failed: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');
    return $db;
}

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
