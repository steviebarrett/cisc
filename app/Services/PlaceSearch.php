<?php
declare(strict_types=1);

namespace App\Services;

use DB;
use PDO;

final class PlaceSearch
{
    public function search(array $params): array
    {
        $q = trim((string)($params['q'] ?? ''));
        $page = max(1, (int)($params['page'] ?? 1));

        $perPage = (int)($params['per_page'] ?? 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $sort = (string)($params['sort'] ?? 'name_asc');

        $orderBy = match ($sort) {
            'count_desc' => 'inf_count DESC, LOWER(p.name) ASC',
            'count_asc'  => 'inf_count ASC, LOWER(p.name) ASC',
            'name_desc'  => 'LOWER(p.name) DESC',
            default      => 'LOWER(p.name) ASC',
        };

        $where = [];
        $bind = [];

        if ($q !== '') {
            $where[] = '(p.name LIKE :q OR p.county LIKE :q)';
            $bind[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $countSql = "
            SELECT COUNT(*) AS c
            FROM place p
            {$whereSql}
        ";

        $sql = "
            SELECT
                p.id,
                p.name,
                p.county,
                p.latitude,
                p.longitude,
                COUNT(DISTINCT CASE WHEN i.place_canada_id = p.id THEN i.informant_id END) AS canada_count,
                COUNT(DISTINCT CASE WHEN i.place_scotland_id = p.id THEN i.informant_id END) AS scotland_count,
                COUNT(DISTINCT i.informant_id) AS inf_count
            FROM place p
            LEFT JOIN informant i
                ON i.place_canada_id = p.id
                OR i.place_scotland_id = p.id
            {$whereSql}
            GROUP BY
                p.id, p.name, p.county, p.latitude, p.longitude
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";

        $db = DB::pdo();

        $countStmt = $db->prepare($countSql);
        foreach ($bind as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $total = (int)($countStmt->fetchColumn() ?? 0);

        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) {
            $page = $pages;
        }
        $offset = ($page - 1) * $perPage;

        $stmt = $db->prepare($sql);
        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach informants for the returned places only
        $placeIds = array_map(
            static fn(array $row): int => (int)$row['id'],
            $rows
        );

        $informantsByPlace = $this->fetchInformantsByPlaceIds($placeIds);

        foreach ($rows as &$row) {
            $placeId = (int)$row['id'];
            $row['informants'] = $informantsByPlace[$placeId] ?? [];
        }
        unset($row);

        $informantIds = [];
        foreach ($rows as $row) {
            foreach (($row['informants'] ?? []) as $inf) {
                $informantIds[] = (string)$inf['informant_id'];
            }
        }

        $recordingsByInformant = $this->fetchRecordingsByInformantIds($informantIds);

        foreach ($rows as &$row) {
            foreach ($row['informants'] as &$inf) {
                $iid = (string)$inf['informant_id'];
                $inf['recordings'] = $recordingsByInformant[$iid] ?? [];
            }
            unset($inf);
        }
        unset($row);

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'per_page' => $perPage,
            'sort' => $sort,
            'q' => $q,
        ];
    }

    /**
     * @param int[] $placeIds
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function fetchInformantsByPlaceIds(array $placeIds): array
    {
        if ($placeIds === []) {
            return [];
        }

        $db = DB::pdo();

        $placeIds = array_values(array_unique(array_map('intval', $placeIds)));

        $phCanada = [];
        $phScotland = [];
        $bind = [];

        foreach ($placeIds as $idx => $id) {
            $k1 = ':pc' . $idx;
            $k2 = ':ps' . $idx;

            $phCanada[] = $k1;
            $phScotland[] = $k2;

            $bind[$k1] = $id;
            $bind[$k2] = $id;
        }

        $sql = "
        SELECT
            i.informant_id,
            CONCAT(i.first_name, ' ', i.last_name) AS name_en,
            CONCAT(i.ainm, ' ', i.cinneadh) AS name_gd,
            i.tradition_scotland,
            i.place_canada_id,
            i.place_scotland_id
        FROM informant i
        WHERE i.place_canada_id IN (" . implode(',', $phCanada) . ")
           OR i.place_scotland_id IN (" . implode(',', $phScotland) . ")
        ORDER BY i.last_name, i.first_name, i.informant_id
    ";

        $stmt = $db->prepare($sql);
        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];

        foreach ($rows as $row) {
            $base = [
                'informant_id' => (string)$row['informant_id'],
                'name_en' => (string)($row['name_en'] ?? ''),
                'name_gd' => (string)($row['name_gd'] ?? ''),
                'tradition_scotland' => $row['tradition_scotland'],
                'place_canada_id' => (int)$row['place_canada_id'] ?? null,
                'place_scotland_id' => (int)$row['place_scotland_id'] ?? null

            ];

            $canadaId = isset($row['place_canada_id']) ? (int)$row['place_canada_id'] : null;
            $scotlandId = isset($row['place_scotland_id']) ? (int)$row['place_scotland_id'] : null;

            if ($canadaId !== null && in_array($canadaId, $placeIds, true)) {
                $entry = $base;
                $entry['relation'] = 'canada';
                $out[$canadaId][] = $entry;
            }

            if ($scotlandId !== null && in_array($scotlandId, $placeIds, true)) {
                $entry = $base;
                $entry['relation'] = 'scotland';
                $out[$scotlandId][] = $entry;
            }
        }

        return $out;
    }


    /**
     * @param string[] $informantIds
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function fetchRecordingsByInformantIds(array $informantIds): array
    {
        if ($informantIds === []) {
            return [];
        }

        $db = DB::pdo();

        $informantIds = array_values(array_unique(array_filter(array_map('strval', $informantIds))));
        $ph = [];
        $bind = [];

        foreach ($informantIds as $idx => $id) {
            $key = ':i' . $idx;
            $ph[] = $key;
            $bind[$key] = $id;
        }

        $sql = "
        SELECT
            r.informant_id,
            r.recording_id,
            r.title,
            r.recording_date
        FROM recording r
        WHERE r.informant_id IN (" . implode(',', $ph) . ")
        ORDER BY r.informant_id, r.recording_date DESC, r.title ASC, r.recording_id ASC
    ";

        $stmt = $db->prepare($sql);
        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $out = [];
        foreach ($rows as $row) {
            $iid = (string)$row['informant_id'];
            $out[$iid][] = [
                'recording_id' => (string)$row['recording_id'],
                'title' => (string)($row['title'] ?? ''),
                'recording_date' => $row['recording_date'],
            ];
        }

        return $out;
    }
}