<?php
// ============================================
// FLINT CAFE - Database Configuration
// ============================================

define('DB_HOST', 'db');
define('DB_NAME', 'flintcafe');
define('DB_USER', 'cafe_user');
define('DB_PASS', 'cafepassword');
define('DB_CHARSET', 'utf8mb4');

define('CAFE_NAME', 'Flint Cafe');
define('CAFE_TAGLINE', 'Where Every Sip Tells a Story');
define('TAX_RATE', 0.11); // 11% PPN

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function generateOrderCode(): string {
    return 'FLT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

function formatRupiah(float $amount): string {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

session_start();
