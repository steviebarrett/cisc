<?php

declare(strict_types=1);

namespace App\Services;

use DB;

final class RecordingSearch
{
    public function search(array $params): array
    {
        $q     = trim((string)($params['q'] ?? ''));
        $place = trim((string)($params['place'] ?? ''));

        $genre = trim((string)($params['genre'] ?? ''));

        $hasTranscription = (int)($params['has_transcription'] ?? 0);
        $transcriptionQ = trim((string)($params['transcription_q'] ?? ''));

        $subgenres = $params['subgenre'] ?? [];
        if (!is_array($subgenres)) {
            $subgenres = [];
        }

        $subjects = $params['subject'] ?? [];
        if (!is_array($subjects)) {
            $subjects = [];
        }

        $hasEn = (int)($params['has_en'] ?? 0);
        $sort  = (string)($params['sort'] ?? 'date_desc');

        // per-page whitelist
        $perPage = (int)($params['per_page'] ?? 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $page = max(1, (int)($params['page'] ?? 1));

        $where = [];
        $bind  = [];

        if ($q !== '') {
            $where[] = "CONCAT_WS(' ', r.title, r.alt_title, r.first_line_chorus, r.first_line_verse, r.notes1_additional_info,
                i.biography_text, c.biography_text) LIKE :q";
            $bind[':q'] = '%' . $q . '%';
        }

        if ($place !== '') {
            $placeExpr = "TRIM(COALESCE(NULLIF(CONCAT_WS(', ', NULLIF(i.community_origin_canada,''), NULLIF(i.county,''), NULLIF(i.province_canada,'')), ''), NULLIF(CONCAT_WS(', ', NULLIF(i.province_canada,''), NULLIF(i.country,'')), ''), NULLIF(i.country,'')))";

            $where[] = "(r.place_of_origin LIKE :place1 OR {$placeExpr} LIKE :place2 OR i.tradition_scotland LIKE :place3)";
            $bind[':place1'] = '%' . $place . '%';
            $bind[':place2'] = '%' . $place . '%';
            $bind[':place3'] = '%' . $place . '%';
        }

        if ($genre !== '') {
            $where[] = 'g.name = :genre';
            $bind[':genre'] = $genre;
        }

        if (count($subgenres) > 0) {
            $placeholders = [];
            foreach ($subgenres as $idx => $sg) {
                $ph = ":subgenre{$idx}";
                $placeholders[] = $ph;
                $bind[$ph] = $sg;
            }

            $where[] = 'EXISTS (
                SELECT 1
                FROM recording_subgenre rs
                JOIN subgenre sg ON sg.subgenre_id = rs.subgenre_id
                WHERE rs.recording_id = r.recording_id
                  AND sg.name IN (' . implode(',', $placeholders) . ')
            )';
        }

        if (count($subjects) > 0) {
            $placeholders = [];
            foreach ($subjects as $idx => $s) {
                $ph = ":subject{$idx}";
                $placeholders[] = $ph;
                $bind[$ph] = $s;
            }

            $where[] = 'EXISTS (
                SELECT 1
                FROM recording_subject rs
                JOIN subject s ON s.subject_id = rs.subject_id
                WHERE rs.recording_id = r.recording_id
                  AND s.name IN (' . implode(',', $placeholders) . ')
            )';
        }

        if ($hasEn === 1) {
            $where[] = 'r.includes_english_translation = 1';
        }

        if ($hasTranscription === 1) {
            $where[] = "r.transcription_html IS NOT NULL AND TRIM(r.transcription_html) <> ''";
        }

        if ($transcriptionQ !== '') {
            $where[] = "COALESCE(r.transcription_text, '') LIKE :transcription_q";
            $bind[':transcription_q'] = '%' . $transcriptionQ . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $orderBy = match ($sort) {
            'date_asc'   => 'r.recording_date ASC',
            'date_desc'  => 'r.recording_date DESC',
            'title_asc'  => 'r.title ASC',
            'title_desc' => 'r.title DESC',
            default      => 'r.recording_date DESC',
        };

        $sql = "
            SELECT r.*,
                   TRIM(CONCAT_WS(' ', i.first_name, i.last_name)) AS informant_name,
                   g.name AS genre_name,
                (SELECT GROUP_CONCAT(sg.name ORDER BY sg.name SEPARATOR ', ')
                     FROM recording_subgenre rs
                     JOIN subgenre sg ON sg.subgenre_id = rs.subgenre_id
                     WHERE rs.recording_id = r.recording_id
                ) AS subgenres,
                (SELECT GROUP_CONCAT(s.name ORDER BY s.name SEPARATOR ', ')
                     FROM recording_subject rs
                     JOIN subject s ON s.subject_id = rs.subject_id
                     WHERE rs.recording_id = r.recording_id
                ) AS subjects
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            LEFT JOIN composer c ON r.composer_id = c.composer_id
            LEFT JOIN genre g ON g.genre_id = r.genre_id
            $whereSql
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ";

        $countSql = "
            SELECT COUNT(*) AS c
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            LEFT JOIN composer c ON r.composer_id = c.composer_id
            LEFT JOIN genre g ON g.genre_id = r.genre_id
            $whereSql
        ";

        $filterBindsForSql = function (string $sql, array $bind): array {
            preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $sql, $m);
            $needed = array_unique($m[0] ?? []);
            $out = [];
            foreach ($needed as $ph) {
                if (array_key_exists($ph, $bind)) {
                    $out[$ph] = $bind[$ph];
                }
            }
            return $out;
        };

        $db = DB::pdo();

        // Keep COUNT bindings strictly to placeholders used in $countSql.
        // Some drivers throw HY093 if you bind params not present in the statement.
        $bindCount = $bind;
        unset($bindCount[':limit'], $bindCount[':offset']);

        // COUNT (no limit/offset)
        $countStmt = $db->prepare($countSql);

        $bindCount = $filterBindsForSql($countSql, $bind);

        foreach ($bindCount as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int)($countStmt->fetchColumn() ?? 0);

        // Compute pages and clamp page
        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        // RESULTS
        $stmt = $db->prepare($sql);

        $bindRows = $filterBindsForSql($sql, $bind);

        foreach ($bindRows as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total'    => $total,
            'rows'     => $rows,
            'page'     => $page,
            'pages'    => $pages,
            'per_page' => $perPage,

            'q'     => $q,
            'place' => $place,
            'genre' => $genre,

            // return both singular + plural for robustness
            'subgenre'  => $subgenres,
            'subgenres' => $subgenres,
            'subject'   => $subjects,
            'subjects'  => $subjects,

            'has_en' => $hasEn,
            'sort'   => $sort,
        ];
    }
}