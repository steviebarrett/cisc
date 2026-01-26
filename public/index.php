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
    ['GET', '#^/informants/([^/]+)/?$#', fn($id) => (new InformantController())->show($id)],
    ['GET', '#^/composers/([^/]+)/?$#', fn($id) => (new ComposerController())->show($id)],
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

foreach ($routes as [$m, $re, $handler]) {
    if ($m !== $method) continue;
    if (preg_match($re, $path, $matches)) {
        array_shift($matches);
        $handler(...$matches);
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