<?php
declare(strict_types=1);

final class Taxonomy {
    public static function genres(): array {
        return DB::pdo()->query("SELECT name FROM genre ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function subgenres(): array {
        return DB::pdo()->query("SELECT name FROM subgenre ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function subgenresByGenre(): array {
        $sql = "
            SELECT g.name AS genre_name, sg.name AS subgenre_name
            FROM recording r
            JOIN genre g ON g.genre_id = r.genre_id
            JOIN recording_subgenre rs ON rs.recording_id = r.recording_id
            JOIN subgenre sg ON sg.subgenre_id = rs.subgenre_id
            WHERE g.name IS NOT NULL
              AND g.name <> ''
              AND sg.name IS NOT NULL
              AND sg.name <> ''
            GROUP BY g.name, sg.name
            ORDER BY g.name, sg.name
        ";

        $rows = DB::pdo()->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $genreName = trim((string)($row['genre_name'] ?? ''));
            $subgenreName = trim((string)($row['subgenre_name'] ?? ''));
            if ($genreName === '' || $subgenreName === '') {
                continue;
            }

            if (!isset($map[$genreName])) {
                $map[$genreName] = [];
            }
            $map[$genreName][] = $subgenreName;
        }

        return $map;
    }

    public static function subjects(): array {
        return DB::pdo()->query("SELECT name FROM subject ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function places(int $limit = 1500): array
    {
        $pdo = DB::pdo();

        $canadaExpr = "TRIM(CONCAT_WS(', ',
        NULLIF(i.community_origin_canada, ''),
        NULLIF(i.county, ''),
        NULLIF(i.province_canada, '')
    ))";

        $scotlandExpr = "TRIM(NULLIF(i.tradition_scotland, ''))";

        $sql = "
        SELECT place
        FROM (
            SELECT {$canadaExpr} AS place
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id

            UNION

            SELECT {$scotlandExpr} AS place
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
        ) p
        WHERE p.place IS NOT NULL
          AND p.place <> ''
        ORDER BY LOWER(place)
        LIMIT :limit
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
