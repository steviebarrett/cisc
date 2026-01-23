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
$base = rtrim((require __DIR__ . '/../config/config.php')['app']['base_path'] ?? '', '/');
if ($base && str_starts_with($path, $base)) $path = substr($path, strlen($base)) ?: '/';

$routes = [
    ['GET', '#^/$#', fn() => (new RecordingController())->index()],
    ['GET', '#^/recordings/?$#', fn() => (new RecordingController())->index()],
    ['GET', '#^/recordings/([^/]+)/?$#', fn($id) => (new RecordingController())->show($id)],
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
echo "404 Not Found";