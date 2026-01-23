<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_path(string $path = ''): string {
    $config = require __DIR__ . '/../../config/config.php';
    $bp = rtrim($config['app']['base_path'] ?? '', '/');
    $path = '/' . ltrim($path, '/');
    return $bp . $path;
}

function qs(array $overrides = []): string {
    $q = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null) unset($q[$k]);
        else $q[$k] = $v;
    }
    return http_build_query($q);
}

function get_array(string $key): array {
    $v = $_GET[$key] ?? [];
    if (!is_array($v)) $v = [$v];
    return array_values(array_filter(array_map('trim', $v), fn($x) => $x !== ''));
}