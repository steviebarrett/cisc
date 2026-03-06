<?php
declare(strict_types=1);

function e(?string $s): string {
    return htmlspecialchars($s, ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * Build a clean query string from $_GET-style arrays,
 * removing empty values and empty array elements.
 */
function clean_qs(array $get): string
{
    $out = [];

    foreach ($get as $k => $v) {
        if (is_array($v)) {
            $v = array_values(array_filter(
                $v,
                fn($x) => trim((string)$x) !== ''
            ));
            if ($v === []) continue;
            $out[$k] = $v;
        } else {
            if (trim((string)$v) === '') continue;
            $out[$k] = $v;
        }
    }

    return http_build_query($out);
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

/**
 * Display a keyword in context using HTML
 * Used, for example, when searching transcriptions for a keyword
 *
 * @param string|null $html
 * @param string $q
 * @return string
 * @throws DOMException
 */
function highlight_html_ga(?string $html, string $q): string {
    $html = (string)$html;
    $q = trim($q);

    if ($html === '' || $q === '') {
        return $html;
    }

    $core = ga_highlight_pattern($q);
    if ($core === '') {
        return $html;
    }

    $pattern = '/(' . $core . ')/iu';

    libxml_use_internal_errors(true);

    $dom = new DOMDocument('1.0', 'UTF-8');
    $wrapped = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="__root__">' . $html . '</div></body></html>';
    $wrapped = '<?xml encoding="UTF-8">' . $wrapped;
    $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    $xpath = new DOMXPath($dom);
    $textNodes = $xpath->query('//div[@id="__root__"]//text()[normalize-space(.) != ""]');

    if ($textNodes !== false) {
        $nodes = [];
        foreach ($textNodes as $node) {
            $nodes[] = $node;
        }

        foreach ($nodes as $textNode) {
            $parentName = strtolower($textNode->parentNode->nodeName ?? '');
            if (in_array($parentName, ['script', 'style', 'mark'], true)) {
                continue;
            }

            $text = $textNode->nodeValue ?? '';
            if ($text === '' || !preg_match($pattern, $text)) {
                continue;
            }

            $parts = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);
            if ($parts === false) {
                continue;
            }

            $frag = $dom->createDocumentFragment();

            foreach ($parts as $part) {
                if ($part === '') continue;

                if (preg_match($pattern, $part)) {
                    $mark = $dom->createElement('mark');
                    $mark->appendChild($dom->createTextNode($part));
                    $frag->appendChild($mark);
                } else {
                    $frag->appendChild($dom->createTextNode($part));
                }
            }

            $textNode->parentNode->replaceChild($frag, $textNode);
        }
    }

    $root = $dom->getElementById('__root__');
    if (!$root) {
        return $html;
    }

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $dom->saveHTML($child);
    }

    libxml_clear_errors();
    return $out;
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

function header_filters_open(string $kw, array $params, array $defaults = []): bool {
    if (trim($kw) !== '') return true;

    foreach ($defaults as $key => $default) {
        $v = $params[$key] ?? null;

        // Array defaults mean: open if there are any selections
        if (is_array($default)) {
            if (!empty($v)) return true;
            continue;
        }

        // Treat null/empty string as “not set”
        if ($v === null || $v === '') continue;

        // Numeric defaults
        if (is_int($default)) {
            if ((int)$v !== $default) return true;
            continue;
        }

        // String/bool-ish defaults
        if ((string)$v !== (string)$default) return true;
    }

    return false;
}

?>