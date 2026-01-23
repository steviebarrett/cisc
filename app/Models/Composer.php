<?php
declare(strict_types=1);

final class Composer {
    public static function find(string $id): ?array {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT * FROM composer WHERE composer_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}