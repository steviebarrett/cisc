<?php
declare(strict_types=1);

namespace app\Services;

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
            'count_desc' => 'rec_count DESC, place ASC, place_scotland ASC',
            'count_asc'  => 'rec_count ASC, place ASC, place_scotland ASC',
            'name_desc'  => 'LOWER(COALESCE(place, \'\')) DESC, LOWER(COALESCE(place_scotland, \'\')) DESC',
            default      => 'LOWER(COALESCE(place, \'\')) ASC, LOWER(COALESCE(place_scotland, \'\')) ASC',
        };

        $where = [];
        $bind = [];

        // Only include rows where at least one linked place exists
        $where[] = '(pc.name IS NOT NULL OR ps.name IS NOT NULL)';

        if ($q !== '') {
            $where[] = '(pc.name LIKE :q OR ps.name LIKE :q)';
            $bind[':q'] = '%' . $q . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $countSql = "
            SELECT COUNT(*) AS c
            FROM (
                SELECT
                    pc.name AS place,
                    ps.name AS place_scotland
                FROM recording r
                JOIN informant i ON i.informant_id = r.informant_id
                LEFT JOIN place pc ON pc.id = i.place_canada_id
                LEFT JOIN place ps ON ps.id = i.place_scotland_id
                {$whereSql}
                GROUP BY
                    pc.name,
                    ps.name
            ) x
        ";

        $sql = "
            SELECT
                pc.name AS place,
                ps.name AS place_scotland,
                COUNT(*) AS rec_count,
                MAX(pc.latitude) AS cn_lat,
                MAX(pc.longitude) AS cn_lng,
                MAX(ps.latitude) AS sc_lat,
                MAX(ps.longitude) AS sc_lng
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            LEFT JOIN place pc ON pc.id = i.place_canada_id
            LEFT JOIN place ps ON ps.id = i.place_scotland_id
            {$whereSql}
            GROUP BY
                pc.name,
                ps.name
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
}