<?php
const DB_HOST = 'localhost';
const DB_PORT = '3306';
const DB_NAME = 'royal_florist';
const DB_USER = 'root';
const DB_PASS = '';

date_default_timezone_set('Asia/Jakarta');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // samakan zona waktu MySQL dengan WIB
    $pdo->exec("SET time_zone = '+07:00'");
} catch (PDOException $e) {
    http_response_code(500);
    exit('Koneksi database gagal: ' . htmlspecialchars($e->getMessage()));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function skalar(PDO $pdo, string $sql, array $params = []) {
    try {
        $st = $pdo->prepare($sql);
        $st->execute($params);
        $v = $st->fetchColumn();
        return $v === false || $v === null ? 0 : $v + 0;
    } catch (PDOException $e) {
        return null;
    }
}
