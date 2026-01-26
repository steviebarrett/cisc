<?php
declare(strict_types=1);

final class Informant {
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