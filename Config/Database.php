<?php
declare(strict_types=1);

final class Database {
    private static ?PDO $pdo = null;

    private const DB_HOST = '127.0.0.1';
  
    private const DB_NAME = 'lab';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    private function __construct() {}

    public static function connect(): PDO {
        if (self::$pdo instanceof PDO) return self::$pdo;
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s',
            self::DB_HOST, self::DB_NAME, self::DB_CHARSET);
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            self::$pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, $opt);
            // echo"<script>alert('Database is connected')</script>";
        } catch (PDOException $e) {
            error_log("DB connect failed: ".$e->getMessage(), 3, __DIR__.'/error.log');
            http_response_code(500);
            exit('Database connection failed.');
        }
        return self::$pdo;
    }
}
