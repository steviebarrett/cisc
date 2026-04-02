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

    /*
     * Returns an array of related recordings.
     */
    public static function related(array $rec): array
    {
        $db = DB::pdo();

        $currentId = $rec['recording_id'];
        $usedIds = [$currentId];

        $sameInformant = null;
        if (!empty($rec['informant_id'])) {
            $stmt = $db->prepare("
            SELECT r.recording_id, r.title as recording_title, r.informant_id, r.genre_id, 
                   CONCAT(i.first_name, ' ', i.last_name) AS informant_name
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            WHERE r.informant_id = :informant_id
              AND r.recording_id <> :current_id
            ORDER BY RAND()
            LIMIT 1
        ");
            $stmt->execute([
                ':informant_id' => $rec['informant_id'],
                ':current_id' => $currentId,
            ]);
            $sameInformant = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($sameInformant) {
                $usedIds[] = $sameInformant['recording_id'];
            }
        }

        $sameGenre = null;
        if (!empty($rec['genre_id'])) {
            $placeholders = implode(',', array_fill(0, count($usedIds), '?'));

            $sql = "
            SELECT r.recording_id, r.title, r.informant_id, r.genre_id, g.name as genre_name, r.title as recording_title
            FROM recording r
            JOIN genre g ON g.genre_id = r.genre_id
            WHERE r.genre_id = ?
              AND r.recording_id NOT IN ($placeholders)
            ORDER BY RAND()
            LIMIT 1
        ";

            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge([$rec['genre_id']], $usedIds));
            $sameGenre = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

            if ($sameGenre) {
                $usedIds[] = $sameGenre['recording_id'];
            }
        }

        $sameSubject = null;
        $stmt = $db->prepare("
        SELECT DISTINCT rs.subject_id
        FROM recording_subject rs
        WHERE rs.recording_id = ?
    ");
        $stmt->execute([$currentId]);
        $subjectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if ($subjectIds) {
            $subjectPlaceholders = implode(',', array_fill(0, count($subjectIds), '?'));
            $usedPlaceholders = implode(',', array_fill(0, count($usedIds), '?'));

            $sql = "
            SELECT r.recording_id, r.title as recording_title, r.informant_id, r.genre_id, s.name as subject_name
            FROM recording r
            JOIN recording_subject rs ON rs.recording_id = r.recording_id
            JOIN subject s ON s.subject_id = rs.subject_id
            WHERE rs.subject_id IN ($subjectPlaceholders)
              AND r.recording_id NOT IN ($usedPlaceholders)
            GROUP BY r.recording_id, r.title, r.informant_id, r.genre_id
            ORDER BY RAND()
            LIMIT 1
        ";

            $stmt = $db->prepare($sql);
            $stmt->execute(array_merge($subjectIds, $usedIds));
            $sameSubject = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        return [
            'same_informant' => $sameInformant,
            'same_genre' => $sameGenre,
            'same_subject' => $sameSubject,
        ];
    }
}