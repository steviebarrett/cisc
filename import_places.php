<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function usage(): void {
    fwrite(STDERR, <<<TXT
Usage:
  php import_places.php --file="/path/to/file.xlsx" --dsn="mysql:host=127.0.0.1;dbname=cisc;charset=utf8mb4" --user="USER" --pass="PASS" [--dry-run]

TXT);
}

function opts(): array {
    return getopt('', ['file:', 'dsn:', 'user:', 'pass:', 'dry-run']);
}

function requireArg(array $o, string $k): string {
    if (empty($o[$k])) {
        usage();
        throw new RuntimeException("Missing required argument --$k");
    }
    return (string)$o[$k];
}

function pdoConnect(string $dsn, string $user, string $pass): PDO {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
    return $pdo;
}

function normHeader(?string $h): string {
    $h = trim((string)$h);
    $h = mb_strtolower($h);
    $h = preg_replace('/\s+/', ' ', $h);
    return trim((string)$h);
}

function cellStr($v): ?string {
    if ($v === null) return null;
    $s = trim((string)$v);
    return $s === '' ? null : $s;
}

function cellFloat($v): ?float {
    if ($v === null || $v === '') return null;
    if (is_numeric($v)) return (float)$v;
    $s = trim((string)$v);
    return is_numeric($s) ? (float)$s : null;
}

function upsertPlace(PDO $pdo, array $r): void {
    $sql = <<<SQL
INSERT INTO place (id, name, county, latitude, longitude)
VALUES (:id, :name, :county, :latitude, :longitude)
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  county = VALUES(county),
  latitude = VALUES(latitude),
  longitude = VALUES(longitude)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $r['id'],
        ':name' => $r['name'],
        ':county' => $r['county'],
        ':latitude' => $r['latitude'],
        ':longitude' => $r['longitude'],
    ]);
}

$o = opts();

try {
    $file = requireArg($o, 'file');
    $dsn  = requireArg($o, 'dsn');
    $user = requireArg($o, 'user');
    $pass = requireArg($o, 'pass');
} catch (\Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}

$dryRun = array_key_exists('dry-run', $o);

if (!is_file($file)) {
    fwrite(STDERR, "File not found: $file\n");
    exit(1);
}

$ss = IOFactory::load($file);
$ws = $ss->getSheet(0); // first sheet
$rows = $ws->toArray(null, true, true, true);

if (!$rows) {
    fwrite(STDERR, "No rows found in workbook\n");
    exit(1);
}

$headerRow = $rows[1] ?? [];
$headers = [];
foreach ($headerRow as $col => $val) {
    $h = normHeader((string)$val);
    if ($h !== '') {
        $headers[$col] = $h;
    }
}

$needed = [
    'place number',
    'community of origin (canada)',
    'county (canada)',
    'latitiude',   // typo in sheet
    'longitude',
];

foreach ($needed as $need) {
    if (!in_array($need, $headers, true)) {
        fwrite(STDERR, "Missing expected header: {$need}\n");
        exit(1);
    }
}

$pdo = pdoConnect($dsn, $user, $pass);

$countSeen = 0;
$countImported = 0;
$countSkipped = 0;

try {
    if (!$dryRun) {
        $pdo->beginTransaction();
    }

    for ($i = 2; $i <= count($rows); $i++) {
        $row = $rows[$i] ?? [];

        $assoc = [];
        foreach ($headers as $col => $h) {
            $assoc[$h] = $row[$col] ?? null;
        }

        $sheetIdRaw = $assoc['place number'] ?? null;
        $sheetId = cellStr($sheetIdRaw);

        // Skip rows with no Place Number
        if ($sheetId === null || !ctype_digit($sheetId)) {
            $countSkipped++;
            continue;
        }

        $id = (int)$sheetId;

        $name = cellStr($assoc['community of origin (canada)'] ?? null);
        $county = cellStr($assoc['county (canada)'] ?? null);
        $lat = cellFloat($assoc['latitiude'] ?? null);
        $lng = cellFloat($assoc['longitude'] ?? null);

        // Skip incomplete rows
        if ($name === null || $lat === null || $lng === null) {
            $countSkipped++;
            fwrite(STDERR, "Skipping row {$i}: missing name and/or coordinates for Place Number {$id}\n");
            continue;
        }

        $countSeen++;

        $payload = [
            'id' => $id,
            'name' => $name,
            'county' => $county,
            'latitude' => $lat,
            'longitude' => $lng,
        ];

        if ($dryRun) {
            echo "[DRY] place {$id}: {$name}" . ($county ? " ({$county})" : "") . " {$lat}, {$lng}\n";
        } else {
            upsertPlace($pdo, $payload);
        }

        $countImported++;
    }

    if (!$dryRun) {
        $pdo->commit();
        echo "✅ Imported {$countImported} place rows.\n";
    } else {
        echo "✅ Dry run complete. {$countImported} rows would be imported.\n";
    }

    if ($countSkipped > 0) {
        echo "Skipped rows: {$countSkipped}\n";
    }

} catch (\Throwable $e) {
    if (!$dryRun && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "❌ Import failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}