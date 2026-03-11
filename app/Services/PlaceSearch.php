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
                COUNT(DISTINCT i.informant_id) AS inf_count,
                GROUP_CONCAT(
                    DISTINCT TRIM(CONCAT_WS(' ', i.first_name, i.last_name))
                    ORDER BY i.last_name, i.first_name
                    SEPARATOR ', '
                ) AS inf_name
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