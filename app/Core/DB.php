<?php
declare(strict_types=1);

final class DB {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo) return self::$pdo;

        $config = require __DIR__ . '/../../config/config.php';
        $db = $config['db'];

        self::$pdo = new PDO($db['dsn'], $db['user'], $db['pass'], $db['options']);
        self::$pdo->exec("SET NAMES utf8mb4");
        return self::$pdo;
    }
}