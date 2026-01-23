<?php
declare(strict_types=1);

final class Taxonomy {
    public static function genres(): array {
        return DB::pdo()->query("SELECT name FROM genre ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function subgenres(): array {
        return DB::pdo()->query("SELECT name FROM subgenre ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function subjects(): array {
        return DB::pdo()->query("SELECT name FROM subject ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }
}