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

        }

        // get the current recording's subject
        $stmt = $db->prepare("
            SELECT s.subject_id, s.name
            FROM recording_subject rs
            JOIN subject s ON s.subject_id = rs.subject_id
            WHERE rs.recording_id = ?
              AND EXISTS (
                  SELECT 1
                  FROM recording_subject rs2
                  WHERE rs2.subject_id = rs.subject_id
                    AND rs2.recording_id <> rs.recording_id
              )
            ORDER BY RAND()
            LIMIT 1
        ");
        $stmt->execute([$currentId]);
        $chosenSubject = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;


        $sameSubject = null;

        // get recording with the same subject
        if ($chosenSubject) {
            $sql = "
                SELECT 
                    r.recording_id,
                    r.title AS recording_title,
                    r.informant_id,
                    r.genre_id,
                    s.name AS subject_name
                FROM recording r
                JOIN recording_subject rs ON rs.recording_id = r.recording_id
                JOIN subject s ON s.subject_id = rs.subject_id
                WHERE rs.subject_id = ?
                  AND r.recording_id != ?
                ORDER BY RAND()
                LIMIT 1
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute([$chosenSubject['subject_id'], $rec['recording_id']]);
            $sameSubject = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        }

        return [
            'same_informant' => $sameInformant,
            'same_genre' => $sameGenre,
            'same_subject' => $sameSubject,
        ];
    }
}