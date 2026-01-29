<?php

declare(strict_types=1);

require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../app/Core/DB.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Core/Controller.php';

require __DIR__ . '/../app/Services/RecordingSearch.php';

require __DIR__ . '/../app/Models/Taxonomy.php';
require __DIR__ . '/../app/Models/Recording.php';
require __DIR__ . '/../app/Models/Informant.php';
require __DIR__ . '/../app/Models/Composer.php';

require __DIR__ . '/../app/Controllers/RecordingController.php';
require __DIR__ . '/../app/Controllers/InformantController.php';
require __DIR__ . '/../app/Controllers/ComposerController.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$config = require __DIR__ . '/../config/config.php';
$base = rtrim($config['app']['base_path'] ?? '', '/');
if ($base && str_starts_with($path, $base)) $path = substr($path, strlen($base)) ?: '/';

function stream_informant_image(string $id, string $fileEnc, bool $headOnly = false): void
{
    // Informant ID must be simple
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $id)) {
        http_response_code(400);
        echo 'Bad informant id';
        return;
    }

    // Decode URL filename (turn %20 into space, %2C into comma, etc.)
    $file = rawurldecode($fileEnc);

    // Allow spaces/commas/etc but block traversal / weirdness
    if (
        $file === '' ||
        $file === '.' || $file === '..' ||
        str_contains($file, '/') ||
        str_contains($file, '\\') ||
        str_contains($file, "\0") ||
        $file !== basename($file)
    ) {
        http_response_code(400);
        echo 'Bad filename: ' . $file;
        return;
    }

    // Only allow image extensions
    if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
        http_response_code(400);
        echo 'Bad extension';
        return;
    }

    $base = rtrim(INFORMANT_IMAGE_PATH, '/');
    $dirs = glob($base . '/' . $id . '*', GLOB_ONLYDIR) ?: [];

    foreach ($dirs as $dir) {
        $candidate = $dir . '/' . $file;   // NOTE: decoded filename used here
        if (is_file($candidate) && is_readable($candidate)) {

            $ext = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'application/octet-stream',
            };

            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($candidate));
            header('Cache-Control: public, max-age=3600');

            if (!$headOnly) {
                readfile($candidate);
            }
            return;
        }
    }

    http_response_code(404);
    echo 'Image not found';
}

function stream_mp3(string $id, bool $headOnly = false): void
{
    if (!preg_match('/^[A-Za-z0-9._-]+$/', $id)) {
        http_response_code(400);
        echo 'Bad request';
        return;
    }

    $file = rtrim(MP3_AUDIO_PATH, '/') . '/' . $id . '.mp3';
    if (!is_file($file) || !is_readable($file)) {
        http_response_code(404);
        echo 'Not found';
        return;
    }

    $size = filesize($file);
    if ($size === false) {
        http_response_code(500);
        echo 'Server error';
        return;
    }

    header('Content-Type: audio/mpeg');
    header('Accept-Ranges: bytes');
    $disposition = (($_GET['download'] ?? '') === '1') ? 'attachment' : 'inline';
    header('Content-Disposition: ' . $disposition . '; filename="' . basename($file) . '"');

    $range = $_SERVER['HTTP_RANGE'] ?? '';
    $start = 0;
    $end = $size - 1;
    $status = 200;

    if ($range && preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $m)) {
        $rStart = $m[1] === '' ? null : (int)$m[1];
        $rEnd   = $m[2] === '' ? null : (int)$m[2];

        if ($rStart === null && $rEnd !== null) {
            $suffix = max(0, $rEnd);
            $start = max(0, $size - $suffix);
        } else {
            if ($rStart !== null) $start = $rStart;
            if ($rEnd !== null) $end = $rEnd;
        }

        $start = max(0, min($start, $size - 1));
        $end   = max($start, min($end, $size - 1));
        $status = 206;
    }

    $length = ($end - $start) + 1;

    if ($status === 206) {
        http_response_code(206);
        header("Content-Range: bytes {$start}-{$end}/{$size}");
        header("Content-Length: {$length}");
    } else {
        header("Content-Length: {$size}");
    }

    if ($headOnly) return;

    $fh = fopen($file, 'rb');
    if ($fh === false) {
        http_response_code(500);
        echo 'Server error';
        return;
    }

    if ($start > 0) fseek($fh, $start);

    $chunk = 8192;
    $remaining = $length;
    while ($remaining > 0 && !feof($fh)) {
        $read = ($remaining > $chunk) ? $chunk : $remaining;
        $buf = fread($fh, $read);
        if ($buf === false) break;
        echo $buf;
        $remaining -= strlen($buf);
        flush();
    }
    fclose($fh);
}

$routes = [
    ['GET', '#^/$#', fn() => (new RecordingController())->index()],
    ['GET', '#^/recordings/?$#', fn() => (new RecordingController())->index()],
    ['GET', '#^/recordings/([^/]+)/?$#', fn($id) => (new RecordingController())->show($id)],

    ['GET',  '#^/media/audio/([^/]+)\.mp3$#', fn($id) => stream_mp3($id)],
    ['HEAD', '#^/media/audio/([^/]+)\.mp3$#', fn($id) => stream_mp3($id, true)],

    ['GET', '#^/informants/?$#', fn() => (new InformantController())->index()],
    ['GET', '#^/informants/([^/]+)/?$#', fn($id) => (new InformantController())->show($id)],

    ['GET', '#^/composers/([^/]+)/?$#', fn($id) => (new ComposerController())->show($id)],

    ['GET',  '#^/media/informants/([^/]+)/([^/]+)$#', fn($id, $file) => stream_informant_image($id, $file)],
    ['HEAD', '#^/media/informants/([^/]+)/([^/]+)$#', fn($id, $file) => stream_informant_image($id, $file, true)],

    ['GET', '#^/places/?$#', function () {
        $pdo = DB::pdo();
        $params = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20),
            'sort' => (string)($_GET['sort'] ?? 'name_asc'),
        ];

        $q = $params['q'];
        $page = max(1, (int)$params['page']);
        $perPage = (int)$params['per_page'];
        if (!in_array($perPage, [10,20,50,100], true)) $perPage = 20;

        $sort = $params['sort'];
        $orderBy = match ($sort) {
            'count_desc' => 'rec_count DESC, place ASC',
            'count_asc'  => 'rec_count ASC, place ASC',
            'name_desc'  => 'LOWER(place) DESC',
            default      => 'LOWER(place) ASC',
        };

        // Prefer fine-grained informant locations (community/county/province/country) with fallbacks.
        $placeExpr = "TRIM(COALESCE(NULLIF(CONCAT_WS(', ', NULLIF(i.community_origin_canada,''), NULLIF(i.county,''), NULLIF(i.province_canada,'')), ''), NULLIF(i.tradition_scotland,''), NULLIF(CONCAT_WS(', ', NULLIF(i.province_canada,''), NULLIF(i.country,'')), ''), NULLIF(i.country,'')))";

        $where = "WHERE {$placeExpr} IS NOT NULL AND {$placeExpr} <> ''";
        $bind = [];
        if ($q !== '') {
            $where .= " AND {$placeExpr} LIKE :q";
            $bind[':q'] = '%' . $q . '%';
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM (SELECT {$placeExpr} AS place FROM recording r JOIN informant i ON i.informant_id = r.informant_id {$where} GROUP BY {$placeExpr}) x");
        $stmt->execute($bind);
        $total = (int)$stmt->fetchColumn();

        $pages = max(1, (int)ceil($total / $perPage));
        if ($page > $pages) $page = $pages;
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT {$placeExpr} AS place, COUNT(*) AS rec_count
            FROM recording r
            JOIN informant i ON i.informant_id = r.informant_id
            {$where}
            GROUP BY {$placeExpr}
            ORDER BY {$orderBy}
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $pdo->prepare($sql);
        foreach ($bind as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        View::render('places/index', [
            'params' => $params,
            'kw' => $q,
            'result' => [
                'rows' => $rows,
                'total' => $total,
                'page' => $page,
                'pages' => $pages,
                'per_page' => $perPage,
                'sort' => $sort,
                'q' => $q,
            ],
        ]);
    }],
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

foreach ($routes as [$m, $re, $handler]) {
    if ($m !== $method) continue;
    if (preg_match($re, $path, $matches)) {
        array_shift($matches);
        try {
            $handler(...$matches);
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=utf-8');

            if ((bool)ini_get('display_errors')) {
                echo "500 Internal Server Error\n\n";
                echo $e->getMessage() . "\n\n";
                echo $e->getFile() . ':' . $e->getLine() . "\n\n";
                echo $e->getTraceAsString();
            } else {
                echo "500 Internal Server Error";
            }
        }
        exit;
    }
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');

$methodDbg = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uriDbg = $_SERVER['REQUEST_URI'] ?? '';

echo "404 Not Found\n";
echo "method: {$methodDbg}\n";
echo "request_uri: {$uriDbg}\n";
echo "parsed_path: {$path}\n";
echo "base_path_config: {$base}\n";