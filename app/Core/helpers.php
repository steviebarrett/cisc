<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function ga_highlight_pattern(string $q): string {
    $q = trim($q);
    if ($q === '') return '';

    // Build a regex that treats plain vowels as matching accented vowels too.
    // Gaelic mainly uses grave accents, but we include a few common variants.
    $map = [
        'a' => '[aàáâäAÀÁÂÄ]',
        'e' => '[eèéêëEÈÉÊË]',
        'i' => '[iìíîïIÌÍÎÏ]',
        'o' => '[oòóôöOÒÓÔÖ]',
        'u' => '[uùúûüUÙÚÛÜ]',
    ];

    $out = '';
    $chars = preg_split('//u', $q, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($chars as $ch) {
        $lower = mb_strtolower($ch, 'UTF-8');
        if (isset($map[$lower])) {
            $out .= $map[$lower];
        } else {
            $out .= preg_quote($ch, '/');
        }
    }

    return $out;
}

function highlight_ga(?string $text, string $q): string {
    $text = (string)$text;
    $q = trim($q);
    if ($text === '' || $q === '') return e($text);

    $core = ga_highlight_pattern($q);
    if ($core === '') return e($text);

    // Wrap in a capture group so preg_split keeps the matches
    $pattern = '/(' . $core . ')/iu';

    $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    if ($parts === false) return e($text);

    $out = '';
    foreach ($parts as $part) {
        if ($part === '') continue;
        if (preg_match($pattern, $part)) {
            $out .= '<mark>' . e($part) . '</mark>';
        } else {
            $out .= e($part);
        }
    }
    return $out;
}

function highlight_excerpt_ga(?string $text, string $q, int $radius = 80): string {
    $text = (string)$text;
    $q = trim($q);
    if ($text === '' || $q === '') return e($text);

    $core = ga_highlight_pattern($q);
    if ($core === '') return e($text);

    $pattern = '/(' . $core . ')/iu';

    if (!preg_match($pattern, $text, $m, PREG_OFFSET_CAPTURE)) {
        $trimmed = mb_substr($text, 0, $radius * 2, 'UTF-8');
        return e($trimmed) . (mb_strlen($text, 'UTF-8') > mb_strlen($trimmed, 'UTF-8') ? '…' : '');
    }

    $bytePos = $m[0][1];
    $charPos = mb_strlen(substr($text, 0, $bytePos), 'UTF-8');

    $start = max(0, $charPos - $radius);
    $end   = min(mb_strlen($text, 'UTF-8'), $charPos + mb_strlen($m[0][0], 'UTF-8') + $radius);

    $excerpt = mb_substr($text, $start, $end - $start, 'UTF-8');
    $prefix = ($start > 0) ? '…' : '';
    $suffix = ($end < mb_strlen($text, 'UTF-8')) ? '…' : '';

    return $prefix . highlight_ga($excerpt, $q) . $suffix;
}

function base_path(string $path = ''): string {
    static $config = null;
    if ($config === null) {
        $config = require __DIR__ . '/../../config/config.php';
    }
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


?>