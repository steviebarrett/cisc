<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function usage(): void {
    fwrite(STDERR, <<<TXT
Usage:
  php import_informants.php --file="/path/to/file.xlsx" --dsn="mysql:host=127.0.0.1;dbname=cisc;charset=utf8mb4" --user="USER" --pass="PASS" [--dry-run]

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
    $h = str_replace(["\r", "\n", "\t"], ' ', $h);
    $h = preg_replace('/\s+/', ' ', $h);
    return trim((string)$h);
}

function cellStr($v): ?string {
    if ($v === null) return null;
    if (is_string($v)) {
        $s = trim($v);
        return $s === '' ? null : $s;
    }
    $s = trim((string)$v);
    return $s === '' ? null : $s;
}

function cellInt($v): ?int {
    if ($v === null || $v === '') return null;
    if (is_numeric($v)) return (int)$v;
    $s = trim((string)$v);
    if ($s === '') return null;
    return ctype_digit($s) ? (int)$s : null;
}

function upsertInformant(PDO $pdo, array $r): void {
    $sql = <<<SQL
INSERT INTO informant (
    informant_id,
    last_name,
    first_name,
    maiden_name,
    nickname,
    cinneadh,
    sloinneadh_breithe,
    ainm,
    patronymic,
    community_origin_canada,
    county,
    province_canada,
    country,
    tradition_scotland,
    place_canada_id,
    place_scotland_id,
    dates_raw
) VALUES (
    :informant_id,
    :last_name,
    :first_name,
    :maiden_name,
    :nickname,
    :cinneadh,
    :sloinneadh_breithe,
    :ainm,
    :patronymic,
    :community_origin_canada,
    :county,
    :province_canada,
    :country,
    :tradition_scotland,
    :place_canada_id,
    :place_scotland_id,
    :dates_raw
)
ON DUPLICATE KEY UPDATE
    last_name = COALESCE(VALUES(last_name), last_name),
    first_name = COALESCE(VALUES(first_name), first_name),
    maiden_name = COALESCE(VALUES(maiden_name), maiden_name),
    nickname = COALESCE(VALUES(nickname), nickname),
    cinneadh = COALESCE(VALUES(cinneadh), cinneadh),
    sloinneadh_breithe = COALESCE(VALUES(sloinneadh_breithe), sloinneadh_breithe),
    ainm = COALESCE(VALUES(ainm), ainm),
    patronymic = COALESCE(VALUES(patronymic), patronymic),
    community_origin_canada = COALESCE(VALUES(community_origin_canada), community_origin_canada),
    county = COALESCE(VALUES(county), county),
    province_canada = COALESCE(VALUES(province_canada), province_canada),
    country = COALESCE(VALUES(country), country),
    tradition_scotland = COALESCE(VALUES(tradition_scotland), tradition_scotland),
    place_canada_id = COALESCE(VALUES(place_canada_id), place_canada_id),
    place_scotland_id = COALESCE(VALUES(place_scotland_id), place_scotland_id),
    dates_raw = COALESCE(VALUES(dates_raw), dates_raw)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':informant_id' => $r['informant_id'],
        ':last_name' => $r['last_name'],
        ':first_name' => $r['first_name'],
        ':maiden_name' => $r['maiden_name'],
        ':nickname' => $r['nickname'],
        ':cinneadh' => $r['cinneadh'],
        ':sloinneadh_breithe' => $r['sloinneadh_breithe'],
        ':ainm' => $r['ainm'],
        ':patronymic' => $r['patronymic'],
        ':community_origin_canada' => $r['community_origin_canada'],
        ':county' => $r['county'],
        ':province_canada' => $r['province_canada'],
        ':country' => $r['country'],
        ':tradition_scotland' => $r['tradition_scotland'],
        ':place_canada_id' => $r['place_canada_id'],
        ':place_scotland_id' => $r['place_scotland_id'],
        ':dates_raw' => $r['dates_raw'],
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

/*
 * Sheet 5 in Excel UI, zero-based index 4.
 * Adjust if needed.
 */
$ws = $ss->getSheet(4);
$rows = $ws->toArray(null, true, true, true);

if (!$rows) {
    fwrite(STDERR, "No rows found in worksheet\n");
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
    'informant id',
    'informant last name',
    'informant maiden name',
    'informant first name',
    'nickname/familiar name',
    'cinneadh',
    'cinneadh-breithe',
    'ainm',
    'sloinneadh',
    'yob-yod',
    'community of origin (canada)',
    'county (canada)',
    'province (canada)',
    'country',
    'scottish tradition',
    'community#',
    'scottrad1#',
];

foreach ($needed as $need) {
    if (!in_array($need, $headers, true)) {
        fwrite(STDERR, "Missing expected header: {$need}\n");
        exit(1);
    }
}

$pdo = pdoConnect($dsn, $user, $pass);

$countSeen = 0;
$countUpserted = 0;
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

        $informantId = cellStr($assoc['informant id'] ?? null);
        if ($informantId === null) {
            $countSkipped++;
            continue;
        }

        $countSeen++;

        $payload = [
            'informant_id' => $informantId,
            'last_name' => cellStr($assoc['informant last name'] ?? null),
            'first_name' => cellStr($assoc['informant first name'] ?? null),
            'maiden_name' => cellStr($assoc['informant maiden name'] ?? null),
            'nickname' => cellStr($assoc['nickname/familiar name'] ?? null),
            'cinneadh' => cellStr($assoc['cinneadh'] ?? null),
            'sloinneadh_breithe' => cellStr($assoc['cinneadh-breithe'] ?? null),
            'ainm' => cellStr($assoc['ainm'] ?? null),
            'patronymic' => cellStr($assoc['sloinneadh'] ?? null),
            'community_origin_canada' => cellStr($assoc['community of origin (canada)'] ?? null),
            'county' => cellStr($assoc['county (canada)'] ?? null),
            'province_canada' => cellStr($assoc['province (canada)'] ?? null),
            'country' => cellStr($assoc['country'] ?? null),
            'tradition_scotland' => cellStr($assoc['scottish tradition'] ?? null),
            'place_canada_id' => cellInt($assoc['community#'] ?? null),
            'place_scotland_id' => cellInt($assoc['scottrad1#'] ?? null),
            'dates_raw' => cellStr($assoc['yob-yod'] ?? null),
        ];

        if ($dryRun) {
            echo "[DRY] would upsert informant {$informantId}";
            if ($payload['place_canada_id'] !== null) {
                echo " | place_canada_id={$payload['place_canada_id']}";
            }
            if ($payload['place_scotland_id'] !== null) {
                echo " | place_scotland_id={$payload['place_scotland_id']}";
            }
            echo "\n";
        } else {
            upsertInformant($pdo, $payload);
        }

        $countUpserted++;
    }

    if (!$dryRun) {
        $pdo->commit();
        echo "✅ Upserted {$countUpserted} informant rows.\n";
    } else {
        echo "✅ Dry run complete. {$countUpserted} rows would be upserted.\n";
    }

    if ($countSkipped > 0) {
        echo "Skipped rows (no informant ID): {$countSkipped}\n";
    }

} catch (\Throwable $e) {
    if (!$dryRun && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "❌ Import failed: " . $e->getMessage() . PHP_EOL);
    exit(1);
}