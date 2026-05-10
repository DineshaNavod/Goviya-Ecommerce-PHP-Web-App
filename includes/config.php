<?php
// ─────────────────────────────────────────────
//  goviya.lk | Database Configuration
// ─────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // change to your MySQL user
define('DB_PASS', '');             // change to your MySQL password
define('DB_NAME', 'goviya_db');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Goviya.lk');
define('SITE_URL',  'http://localhost/goviya');
define('CURRENCY',  'Rs.');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}
