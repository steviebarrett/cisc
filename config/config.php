<?php
declare(strict_types=1);

// Media paths/URLs (guarded so config can be included multiple times)
if (!defined('MP3_AUDIO_PATH')) {
    define('MP3_AUDIO_PATH', __DIR__ . '/../files/audio/mp3');
}
if (!defined('MP3_AUDIO_URL')) {
    define('MP3_AUDIO_URL',  '/media/audio');
}

if (!defined('COMPOSER_IMAGE_PATH')) {
    define('COMPOSER_IMAGE_PATH', __DIR__ . '/../files/images/people/composers');
}
if (!defined('COMPOSER_IMAGE_URL')) {
    define('COMPOSER_IMAGE_URL',  '/files/images/people/composers');
}

if (!defined('INFORMANT_IMAGE_PATH')) {
    define('INFORMANT_IMAGE_PATH', __DIR__ . '/../files/images/people/informants');
}
if (!defined('INFORMANT_IMAGE_URL')) {
    define('INFORMANT_IMAGE_URL',  '/files/images/people/informants');
}

$base = [
    'db' => [
        'dsn'  => 'mysql:host=localhost;dbname=cisc;charset=utf8mb4',
        'user' => 'root',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ],
    ],
    'app' => [
        // If deployed in a subfolder, set e.g. '/myapp/public'
        'base_path' => '',
    ],
];

// Optional local overrides (never committed)
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;

    // Merge: local wins; handles nested arrays like 'db'
    $base = array_replace_recursive($base, is_array($local) ? $local : []);
}

return $base;
