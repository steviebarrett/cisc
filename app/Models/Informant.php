<?php
declare(strict_types=1);

final class Informant {
    public static function find(string $id): ?array {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT * FROM informant WHERE informant_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $img = $pdo->prepare("SELECT slot, filename, caption FROM informant_image WHERE informant_id = :id ORDER BY slot");
        $img->execute([':id' => $id]);
        $row['images'] = $img->fetchAll();
        return $row;
    }
}