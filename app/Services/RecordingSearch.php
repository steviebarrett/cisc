<?php
declare(strict_types=1);

final class RecordingSearch {
    public function search(array $params): array {
        $pdo = DB::pdo();

        $page = max(1, (int)($params['page'] ?? 1));
        $perPage = max(1, min(100, (int)($params['per_page'] ?? 20)));
        $offset = ($page - 1) * $perPage;

        $q = trim((string)($params['q'] ?? ''));
        $genre = trim((string)($params['genre'] ?? ''));
        $subgenres = $params['subgenres'] ?? [];
        $subjects = $params['subjects'] ?? [];
        $hasEn = (int)($params['has_en'] ?? 0);

        $sort = (string)($params['sort'] ?? 'date_asc');
        $sortSql = match ($sort) {
            'date_desc' => 'r.recording_date DESC, r.recording_id DESC',
            'title_asc' => 'r.title ASC, r.recording_id ASC',
            'title_desc'=> 'r.title DESC, r.recording_id DESC',
            default     => 'r.recording_date ASC, r.recording_id ASC', // date_asc
        };

        $where = [];
        $bind = [];

        if ($q !== '') {
            // Simple keyword search across a few text columns
            // CONCAT_WS ignores NULLs, so this is safe
            $where[] = "CONCAT_WS(' ', r.title, r.alt_title, r.first_line_chorus, r.first_line_verse, r.notes1_additional_info) LIKE :q";
            $bind[':q'] = '%' . $q . '%';
        }

        if ($genre !== '') {
            $where[] = "g.name = :genre";
            $bind[':genre'] = $genre;
        }

        if ($hasEn === 1) {
            $where[] = "r.includes_english_translation = 1";
        }

        if (!empty($subgenres)) {
            $in = [];
            foreach ($subgenres as $i => $name) {
                $ph = ":sg{$i}";
                $in[] = $ph;
                $bind[$ph] = $name;
            }
            $where[] = "EXISTS (
        SELECT 1
        FROM recording_subgenre rs
        JOIN subgenre sg ON sg.subgenre_id = rs.subgenre_id
        WHERE rs.recording_id = r.recording_id
          AND sg.name IN (" . implode(',', $in) . ")
      )";
        }

        if (!empty($subjects)) {
            $in = [];
            foreach ($subjects as $i => $name) {
                $ph = ":sub{$i}";
                $in[] = $ph;
                $bind[$ph] = $name;
            }
            $where[] = "EXISTS (
        SELECT 1
        FROM recording_subject rsub
        JOIN subject s ON s.subject_id = rsub.subject_id
        WHERE rsub.recording_id = r.recording_id
          AND s.name IN (" . implode(',', $in) . ")
      )";
        }

        $whereSql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

        // Total count
        $countSql = "
      SELECT COUNT(*) AS c
      FROM recording r
      LEFT JOIN genre g ON g.genre_id = r.genre_id
      $whereSql
    ";
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($bind);
        $total = (int)$stmt->fetchColumn();

        // Page results
        $sql = "
      SELECT
        r.recording_id, r.title, r.alt_title, r.recording_date, r.includes_english_translation,
        r.place_of_origin, 
        r.first_line_chorus, r.first_line_verse, r.notes1_additional_info,
        i.informant_id, i.first_name AS informant_first, i.last_name AS informant_last,
        c.composer_id, c.first_name AS composer_first, c.last_name AS composer_last,
        g.name AS genre_name,
        ss.name AS structure_name
      FROM recording r
      JOIN informant i ON i.informant_id = r.informant_id
      LEFT JOIN composer c ON c.composer_id = r.composer_id
      LEFT JOIN genre g ON g.genre_id = r.genre_id
      LEFT JOIN song_structure ss ON ss.structure_id = r.song_structure_id
      $whereSql
      ORDER BY $sortSql
      LIMIT :limit OFFSET :offset
    ";
        $stmt = $pdo->prepare($sql);

        foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Attach subgenres + subjects for each row (simple N+1 for starter; we can optimize later)
        foreach ($rows as &$row) {
            $rid = $row['recording_id'];
            $row['subgenres'] = Recording::subgenres($rid);
            $row['subjects']  = Recording::subjects($rid);
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => (int)ceil($total / max(1, $perPage)),
        ];
    }
}