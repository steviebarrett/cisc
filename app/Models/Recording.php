<?php
declare(strict_types=1);

final class Recording {
    public static function find(string $id): ?array {
        $pdo = DB::pdo();
        $sql = "
      SELECT
        r.*,
        g.name AS genre_name,
        ss.name AS structure_name,
        i.informant_id, i.first_name AS informant_first, i.last_name AS informant_last,
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