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

    public static function places(int $limit = 1500): array {
        $limit = max(50, min(5000, $limit));

        $placeExpr = "TRIM(COALESCE(
            NULLIF(CONCAT_WS(', ',
                NULLIF(i.community_origin_canada,''),
                NULLIF(i.county,''),
                NULLIF(i.province_canada,'')
            ), ''),
            NULLIF(i.tradition_scotland,''),
            NULLIF(CONCAT_WS(', ',
                NULLIF(i.province_canada,''),
                NULLIF(i.country,'')
            ), ''),
            NULLIF(i.country,'')
        ))";

        $sql = "
        SELECT {$placeExpr} AS place
        FROM recording r
        JOIN informant i ON i.informant_id = r.informant_id
        WHERE {$placeExpr} IS NOT NULL AND {$placeExpr} <> ''
        GROUP BY {$placeExpr}
        ORDER BY LOWER(place) ASC
        LIMIT :limit
    ";

        $stmt = DB::pdo()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}