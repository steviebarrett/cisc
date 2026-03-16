<?php

declare(strict_types=1);

require __DIR__ . '/../app/Core/helpers.php';
require __DIR__ . '/../app/Core/DB.php';
require __DIR__ . '/../app/Core/View.php';
require __DIR__ . '/../app/Core/Controller.php';

require __DIR__ . '/../app/Services/RecordingSearch.php';
require __DIR__ . '/../app/Services/PlaceSearch.php';

require __DIR__ . '/../app/Models/Taxonomy.php';
require __DIR__ . '/../app/Models/Recording.php';
require __DIR__ . '/../app/Models/Informant.php';
require __DIR__ . '/../app/Models/Composer.php';
require __DIR__ . '/../app/Models/SearchPanel.php';

require __DIR__ . '/../app/Controllers/HomeController.php';
require __DIR__ . '/../app/Controllers/RecordingController.php';
require __DIR__ . '/../app/Controllers/InformantController.php';
require __DIR__ . '/../app/Controllers/ComposerController.php';
require __DIR__ . '/../app/Controllers/PlaceController.php';

//$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';
$path = rawurldecode($path);
$config = require __DIR__ . '/../config/config.php';
$base = rtrim($config['app']['base_path'] ?? '', '/');
if ($base && str_starts_with($path, $base)) $path = substr($path, strlen($base)) ?: '/';

function stream_informant_image(string $fileEnc, bool $headOnly = false): void
{
    // Decode URL filename (turn %20 into space, etc.)
    $file = rawurldecode($fileEnc);

    // Basic validation: must be a single filename (no paths), no nulls, no traversal.
    if (
        $file === '' ||
        $file === '.' || $file === '..' ||
        str_contains($file, '/') ||
        str_contains($file, '\\') ||
        str_contains($file, "\0") ||
        $file !== basename($file)
    ) {
        http_response_code(400);
        echo 'Bad filename';
        return;
    }

    // Allow only image extensions
    if (!preg_match('/\.(jpe?g|png|gif|webp)$/i', $file)) {
        http_response_code(400);
        echo 'Bad extension';
        return;
    }

    $base = rtrim(INFORMANT_IMAGE_PATH, '/');
    $path = $base . '/' . $file;

    if (!is_file($path) || !is_readable($path)) {
        http_response_code(404);
        echo 'Image not found';
        return;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'jpg', 'jpeg' => 'image/jpeg',
        'png'          => 'image/png',
        'gif'          => 'image/gif',
        'webp'         => 'image/webp',
        default        => 'application/octet-stream',
    };

    // Simple caching
    $mtime = filemtime($path) ?: time();
    $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

    // If-Modified-Since handling (optional but helps performance)
    if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $ims = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($ims !== false && $ims >= $mtime) {
            header('Last-Modified: ' . $lastModified);
            http_response_code(304);
            return;
        }
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($path));
    header('Last-Modified: ' . $lastModified);
    header('Cache-Control: public, max-age=3600');

    if (!$headOnly) {
        // Clean output buffers to avoid corruption
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        readfile($path);
    }
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
    ['GET', '#^/$#', fn() => (new HomeController())->index()],
    ['GET', '#^/about$#', fn() => (new HomeController())->about()],
    ['GET', '#^/how_to_use#', fn() => (new HomeController())->how_to_use()],
    ['GET', '#^/thanks$#', fn() => (new HomeController())->thanks()],
    ['GET', '#^/map$#', fn() => (new HomeController())->show_map()],
    ['GET', '#^/recordings/?$#', fn() => (new RecordingController())->index()],
    ['GET', '#^/recordings/([^/]+)/?$#', fn($id) => (new RecordingController())->show($id)],
    ['GET', '#^/recordings/([^/]+)/download-transcription/?$#', fn($id) => (new RecordingController())->downloadTranscription($id)],

    ['GET',  '#^/media/audio/([^/]+)\.mp3$#', fn($id) => stream_mp3($id)],
    ['HEAD', '#^/media/audio/([^/]+)\.mp3$#', fn($id) => stream_mp3($id, true)],

    ['GET', '#^/informants/?$#', fn() => (new InformantController())->index()],
    ['GET', '#^/informants/([^/]+)/?$#', fn($id) => (new InformantController())->show($id)],

    ['GET', '#^/composers/([^/]+)/?$#', fn($id) => (new ComposerController())->show($id)],

    ['GET',  '#^/media/informants/([^/]+\.(?:jpe?g|png|gif|webp))$#i', fn($file) => stream_informant_image($file)],
    ['HEAD', '#^/media/informants/([^/]+\.(?:jpe?g|png|gif|webp))$#i', fn($file) => stream_informant_image($file, true)],

    ['GET', '#^/places/?$#', fn() => (new PlaceController())->index()],
];

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

foreach ($routes as [$m, $re, $handler]) {
    if ($m !== $method) continue;
    if (preg_match($re, $path, $matches)) {
        array_shift($matches);
        try {
            $handler(...$matches);
        } catch (Throwable $e) {
            error_log('CISC ERROR: ' . $e->getMessage());
            error_log('CISC ERROR: ' . $e->getFile() . ':' . $e->getLine());
            error_log('CISC ERROR: ' . $e->getTraceAsString());

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
