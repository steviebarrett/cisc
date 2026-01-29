<?php

declare(strict_types=1);

namespace App\Services;

use DB;

final class RecordingSearch
{
    public function search(array $params): array
    {
        $q = trim((string)($params['q'] ?? ''));
        $place = trim((string)($params['place'] ?? ''));

        $genre = trim((string)($params['genre'] ?? ''));
        $subgenres = $params['subgenre'] ?? [];
        if (!is_array($subgenres)) {
            $subgenres = [];
        }
        $subjects = $params['subject'] ?? [];
        if (!is_array($subjects)) {
            $subjects = [];
        }
        $hasEn = (int)($params['has_en'] ?? 0);
        $sort = $params['sort'] ?? 'date_desc';
        $perPage = (int)($params['per_page'] ?? 20);
        $page = (int)($params['page'] ?? 1);
        $offset = max(0, $page - 1) * $perPage;

        $where = [];
        $bind = [];

        if ($q !== '') {
            // Simple keyword search across a few text columns
            // CONCAT_WS ignores NULLs, so this is safe
            $where[] = "CONCAT_WS(' ', r.title, r.alt_title, r.first_line_chorus, r.first_line_verse, r.notes1_additional_info) LIKE :q";
            $bind[':q'] = '%' . $q . '%';
        }

        if ($place !== '') {
            $placeExpr = "TRIM(COALESCE(NULLIF(CONCAT_WS(', ', NULLIF(i.community_origin_canada,''), NULLIF(i.county,''), NULLIF(i.province_canada,'')), ''), NULLIF(i.tradition_scotland,''), NULLIF(CONCAT_WS(', ', NULLIF(i.province_canada,''), NULLIF(i.country,'')), ''), NULLIF(i.country,'')))";
            $where[] = "(r.place_of_origin LIKE :place OR {$placeExpr} LIKE :place)";
            $bind[':place'] = '%' . $place . '%';
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

        $whereSql = '';
        if (count($where) > 0) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        $orderBy = match ($sort) {
            'date_asc' => 'r.recording_date ASC',
            'date_desc' => 'r.recording_date DESC',
            'title_asc' => 'r.title ASC',
            'title_desc' => 'r.title DESC',
            default => 'r.recording_date DESC',
        };

        $sql = "
            SELECT r.*, g.name AS genre_name,
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
            LEFT JOIN genre g ON g.genre_id = r.genre_id
            $whereSql
            ORDER BY $orderBy
            LIMIT :limit OFFSET :offset
        ";

        $countSql = "
            SELECT COUNT(*) AS c
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            LEFT JOIN genre g ON g.genre_id = r.genre_id
            $whereSql
        ";

        $bind[':limit'] = $perPage;
        $bind[':offset'] = $offset;

        $db = DB::pdo();

        $stmt = $db->prepare($countSql);
        foreach ($bind as $k => $v) {
            if ($k === ':limit' || $k === ':offset') {
                continue;
            }
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $total = (int)($stmt->fetchColumn() ?? 0);

        $stmt = $db->prepare($sql);
        foreach ($bind as $k => $v) {
            if ($k === ':limit' || $k === ':offset') {
                $stmt->bindValue($k, $v, \PDO::PARAM_INT);
            } else {
                $stmt->bindValue($k, $v);
            }
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'total' => $total,
            'rows' => $rows,
            'page' => $page,
            'per_page' => $perPage,
            'q' => $q,
            'place' => $place,
            'genre' => $genre,
            'subgenres' => $subgenres,
            'subjects' => $subjects,
            'has_en' => $hasEn,
            'sort' => $sort,
        ];
    }
}