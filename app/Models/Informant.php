<?php
declare(strict_types=1);

final class Informant {
    public static function randomFeatured(int $limit = 5): array {
        $pdo = DB::pdo();
        $limit = max(1, min(20, $limit));

        $sql = "
        SELECT
            i.informant_id,
            i.first_name,
            i.last_name,
            i.ainm,
            i.cinneadh,
            i.community_origin_canada,
            i.county,
            (
                SELECT ii.filename
                FROM informant_image ii
                WHERE ii.informant_id = i.informant_id
                ORDER BY ii.slot ASC, ii.filename ASC
                LIMIT 1
            ) AS image_filename,
            (
                SELECT COUNT(*)
                FROM recording r
                WHERE r.informant_id = i.informant_id
            ) AS recording_count
        FROM informant i
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
        $stmt = $pdo->prepare("SELECT * FROM informant WHERE informant_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $img = $pdo->prepare("SELECT slot, filename, caption FROM informant_image WHERE informant_id = :id ORDER BY slot");
        $img->execute([':id' => $id]);
        $row['images'] = $img->fetchAll();

        // filesystem images
        $row['fs_images'] = self::findFilesystemImages($id);

        return $row;
    }

    public static function search(array $params): array {
        $pdo = DB::pdo();

        $q = trim((string)($params['q'] ?? ''));
        $page = max(1, (int)($params['page'] ?? 1));

        $perPage = (int)($params['per_page'] ?? 12);
        if (!in_array($perPage, [12,24,48,96,10,20,50,100], true)) $perPage = 12;

        $sort = (string)($params['sort'] ?? 'name_asc');
        $orderBy = match ($sort) {
            'name_desc' => 'i.last_name DESC, i.first_name DESC, i.informant_id DESC',
            default     => 'i.last_name ASC, i.first_name ASC, i.informant_id ASC',
        };

        $where = [];
        $bind = [];

        if ($q !== '') {
            $where[] = "(i.informant_id LIKE :q
                 OR i.first_name LIKE :q
                 OR i.last_name LIKE :q
                 OR i.ainm LIKE :q
                 OR i.cinneadh LIKE :q
                 OR i.community_origin_canada LIKE :q
                 OR i.county LIKE :q
                 OR i.province_canada LIKE :q
                 OR i.country LIKE :q
                 OR i.tradition_scotland LIKE :q)";
            $bind[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        // Total
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM informant i {$whereSql}");
        $stmt->execute($bind);
        $total = (int)$stmt->fetchColumn();

        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $sql = "
        SELECT
            i.informant_id, i.first_name, i.last_name, i.ainm, i.cinneadh,
            i.community_origin_canada, i.county, i.province_canada, i.country, i.tradition_scotland,
            (
                SELECT ii.filename
                FROM informant_image ii
                WHERE ii.informant_id = i.informant_id
                ORDER BY ii.slot ASC, ii.filename ASC
                LIMIT 1
            ) AS image_filename,
            (SELECT COUNT(*) FROM recording r WHERE r.informant_id = i.informant_id) AS recording_count
        FROM informant i
        {$whereSql}
        ORDER BY {$orderBy}
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $pdo->prepare($sql);
        foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

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

    private static function findFilesystemImages(string $id): array
    {
        $base = rtrim(INFORMANT_IMAGE_PATH, '/');
        if ($id === '' || !is_dir($base)) return [];

        // folders named {informant_id}xxxxxx
        $dirs = glob($base . '/' . $id . '*', GLOB_ONLYDIR) ?: [];
        sort($dirs, SORT_STRING);

        $exts = ['jpg','jpeg','png','gif','webp'];
        foreach ($dirs as $dir) {
            $files = [];
            $entries = @scandir($dir);
            if ($entries === false) continue;

            foreach ($entries as $fn) {
                if ($fn === '.' || $fn === '..') continue;
                $path = $dir . '/' . $fn;
                if (!is_file($path)) continue;

                $ext = strtolower(pathinfo($fn, PATHINFO_EXTENSION));
                if (!in_array($ext, $exts, true)) continue;

                $files[] = $fn;
            }

            if ($files) {
                natsort($files);
                return array_values(array_unique($files));
            }
        }

        return [];
    }
}
