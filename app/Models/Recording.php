<?php
declare(strict_types=1);

final class Recording {
    public static function randomFeatured(int $limit = 3): array {
        $pdo = DB::pdo();
        $limit = max(1, min(12, $limit));

        $sql = "
      SELECT
        r.recording_id,
        r.title,
        r.recording_date,
        g.name AS genre_name,
        i.first_name AS informant_first,
        i.last_name AS informant_last
      FROM recording r
      JOIN informant i ON i.informant_id = r.informant_id
      LEFT JOIN genre g ON g.genre_id = r.genre_id
      ORDER BY RAND()
      LIMIT :limit
    ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function find(string $id): ?array {
        $pdo = DB::pdo();
        $sql = "
      SELECT
        r.*,
        g.name AS genre_name,
        ss.name AS structure_name,
        i.informant_id,
        i.first_name AS informant_first,
        i.last_name AS informant_last,
        i.tradition_scotland AS informant_detail_light,
        (
          SELECT COUNT(*)
          FROM recording rr
          WHERE rr.informant_id = i.informant_id
        ) AS informant_recording_count,
        (
          SELECT ii.filename
          FROM informant_image ii
          WHERE ii.informant_id = i.informant_id
          ORDER BY ii.slot ASC, ii.filename ASC
          LIMIT 1
        ) AS informant_image_filename,
        c.composer_id, c.first_name AS composer_first, c.last_name AS composer_last,
        r.transcription_text AS transcription_text, r.transcription_html AS transcription_html
      FROM recording r
      JOIN informant i ON i.informant_id = r.informant_id
      LEFT JOIN composer c ON c.composer_id = r.composer_id
      LEFT JOIN genre g ON g.genre_id = r.genre_id
      LEFT JOIN song_structure ss ON ss.structure_id = r.song_structure_id
      WHERE r.recording_id = :id
      LIMIT 1
    ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $row['subgenres'] = self::subgenres($id);
        $row['subjects']  = self::subjects($id);
        return $row;
    }

      public static function related(string $recordingId, string $informantId = '', string $genreId = '', int $limit = 3): array {
        $pdo = DB::pdo();
        $informantId = trim($informantId);
        $genreId = trim($genreId);
        $limit = max(1, min(12, $limit));

        if ($informantId === '' && $genreId === '') {
          return [];
        }

        $where = ['r.recording_id <> :recording_id'];
        $bind = [':recording_id' => $recordingId];
        $scoreParts = [];
        $matchFilters = [];

        if ($informantId !== '') {
          $matchFilters[] = 'r.informant_id = :informant_id';
          $bind[':informant_id'] = $informantId;
          $scoreParts[] = 'CASE WHEN r.informant_id = :score_informant_id THEN 2 ELSE 0 END';
          $bind[':score_informant_id'] = $informantId;
        }
        if ($genreId !== '') {
          $matchFilters[] = 'r.genre_id = :genre_id';
          $bind[':genre_id'] = $genreId;
          $scoreParts[] = 'CASE WHEN r.genre_id = :score_genre_id THEN 1 ELSE 0 END';
          $bind[':score_genre_id'] = $genreId;
        }

        $scoreSql = implode(' + ', $scoreParts);
        if (!empty($matchFilters)) {
          $where[] = '(' . implode(' OR ', $matchFilters) . ')';
        }
        $whereSql = implode(' AND ', $where);

        $sql = "
        SELECT
        r.recording_id,
        r.title,
        r.recording_date,
        g.name AS genre_name,
        i.first_name AS informant_first,
        i.last_name AS informant_last,
        ({$scoreSql}) AS relation_score
        FROM recording r
        JOIN informant i ON i.informant_id = r.informant_id
        LEFT JOIN genre g ON g.genre_id = r.genre_id
        WHERE ({$whereSql})
        ORDER BY relation_score DESC, r.recording_date DESC, r.recording_id DESC
        LIMIT :limit
      ";

        $stmt = $pdo->prepare($sql);
        foreach ($bind as $k => $v) {
          $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
      }

    public static function subgenres(string $recordingId): array {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
      SELECT sg.name
      FROM recording_subgenre rs
      JOIN subgenre sg ON sg.subgenre_id = rs.subgenre_id
      WHERE rs.recording_id = :id
      ORDER BY sg.name
    ");
        $stmt->execute([':id' => $recordingId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function subjects(string $recordingId): array {
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("
      SELECT s.name
      FROM recording_subject rs
      JOIN subject s ON s.subject_id = rs.subject_id
      WHERE rs.recording_id = :id
      ORDER BY s.name
    ");
        $stmt->execute([':id' => $recordingId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
