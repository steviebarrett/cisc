<?php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * One-off importer for:
 *  - Informant Bios  -> informant + informant_image
 *  - Composer Bios   -> composer
 *  - Collection Recordings Meta-data -> recording + lookups + junctions
 *
 * Usage:
 *  php import_metadata.php --file="/path/to.xlsx" --dsn="mysql:host=localhost;dbname=YOURDB;charset=utf8mb4" --user="root" --pass="secret" [--dry-run] [--truncate]
 */

function usage(): void {
    fwrite(STDERR, <<<TXT
Usage:
  php import_metadata.php --file="docs/sampleData.xlsx" --dsn="mysql:host=localhost;dbname=cisc;charset=utf8mb4" --user="USERNAME" --pass="PASSWORD" [--dry-run] [--truncate]

Options:
  --file       Path to XLSX
  --dsn        PDO DSN for MySQL (include charset=utf8mb4)
  --user       DB username
  --pass       DB password
  --dry-run    Parse everything but don't write to DB
  --truncate   TRUNCATE target tables before importing (DANGEROUS)

Example:
  php import_metadata.php --file="/mnt/data/Sample metadata for Stevie 19.01.25.xlsx" \\
    --dsn="mysql:host=127.0.0.1;dbname=gaelic;charset=utf8mb4" --user="gaelic" --pass="..." --truncate

TXT);
}

function opts(): array {
    return getopt('', ['file:', 'dsn:', 'user:', 'pass:', 'dry-run', 'truncate']);
}

function normHeader(string $h): string {
    $h = trim($h);
    $h = mb_strtolower($h);
    $h = preg_replace('/\s+/', ' ', $h);
    $h = str_replace(["\n", "\r", "\t"], ' ', $h);
    $h = str_replace(['#', ':'], ['', ''], $h);
    $h = trim($h);
    return $h;
}

function cellStr($v): ?string {
    if ($v === null) return null;
    if (is_string($v)) {
        $t = trim($v);
        return $t === '' ? null : $t;
    }
    if (is_bool($v)) return $v ? '1' : '0';
    if (is_numeric($v)) return (string)$v;
    $t = trim((string)$v);
    return $t === '' ? null : $t;
}

function parseDateToYmd($v): ?string {
    if ($v === null || $v === '') return null;

    // Excel numeric date
    if (is_numeric($v)) {
        try {
            $dt = ExcelDate::excelToDateTimeObject((float)$v);
            return $dt->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    $s = trim((string)$v);
    if ($s === '') return null;

    $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'M j, Y', 'F j, Y', 'j M Y', 'j F Y'];
    foreach ($formats as $fmt) {
        $dt = \DateTime::createFromFormat($fmt, $s);
        if ($dt instanceof \DateTime) return $dt->format('Y-m-d');
    }

    $ts = strtotime($s);
    if ($ts !== false) return date('Y-m-d', $ts);

    return null;
}

function splitMulti(?string $s): array {
    if ($s === null) return [];
    $parts = preg_split('/\s*[,;|]\s*/u', $s) ?: [];
    $out = [];
    foreach ($parts as $p) {
        $p = trim($p);
        if ($p !== '') $out[] = $p;
    }
    // unique, preserve order (case-insensitive)
    $seen = [];
    $uniq = [];
    foreach ($out as $v) {
        $k = mb_strtolower($v);
        if (!isset($seen[$k])) {
            $seen[$k] = true;
            $uniq[] = $v;
        }
    }
    return $uniq;
}

function normToken(string $s): string {
    $s = mb_strtolower(trim($s));
    $s = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim($s);
}

function firstNonEmpty(array $row, array $keys): ?string {
    foreach ($keys as $key) {
        if (!array_key_exists($key, $row)) continue;
        $value = cellStr($row[$key]);
        if ($value !== null) return $value;
    }
    return null;
}

function findSheet(\PhpOffice\PhpSpreadsheet\Spreadsheet $ss, array $needles): ?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet {
    $normalizedNeedles = array_map(fn(string $needle): string => normToken($needle), $needles);

    foreach ($ss->getWorksheetIterator() as $ws) {
        $name = normToken($ws->getTitle());
        foreach ($normalizedNeedles as $needle) {
            if ($needle !== '' && str_contains($name, $needle)) return $ws;
        }
    }
    return null;
}

function readSheetRows(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws): array {
    $rows = $ws->toArray(null, true, true, true);
    if (!$rows) return [];

    // header row = first row with >= 5 non-empty cells
    $headerRowIdx = null;
    foreach ($rows as $i => $row) {
        $nonEmpty = 0;
        foreach ($row as $cell) {
            if (cellStr($cell) !== null) $nonEmpty++;
        }
        if ($nonEmpty >= 5) { $headerRowIdx = $i; break; }
    }
    if ($headerRowIdx === null) return [];

    $headerRow = $rows[$headerRowIdx];
    $headers = []; // col => normalized header
    foreach ($headerRow as $col => $h) {
        $hs = cellStr($h);
        if ($hs !== null) $headers[$col] = normHeader($hs);
    }

    $out = [];
    foreach ($rows as $i => $row) {
        if ($i <= $headerRowIdx) continue;

        $assoc = [];
        $allBlank = true;

        foreach ($headers as $col => $hkey) {
            $val = $row[$col] ?? null;
            $valStr = cellStr($val);
            if ($valStr !== null) $allBlank = false;
            $assoc[$hkey] = $valStr;
        }

        if ($allBlank) continue;
        $out[] = $assoc;
    }

    return $out;
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
    // for good measure (dsn should already include charset)
    $pdo->exec("SET NAMES utf8mb4");
    return $pdo;
}

function truncateAll(PDO $pdo): void {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
    // order matters with FKs
    $tables = [
        'recording_subject',
        'recording_subgenre',
        'recording',
        'informant_image',
        'informant',
        'composer',
        'subject',
        'subgenre',
        'song_structure',
        'genre',
    ];
    foreach ($tables as $t) {
        $pdo->exec("TRUNCATE TABLE `$t`");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
}

final class LookupCache {
    /** @var array<string,int> */
    public array $genre = [];
    /** @var array<string,int> */
    public array $structure = [];
    /** @var array<string,int> */
    public array $subgenre = [];
    /** @var array<string,int> */
    public array $subject = [];
}

function getOrCreateLookup(PDO $pdo, LookupCache $cache, string $table, string $name, string $cacheProp, string $idCol): int {
    $key = mb_strtolower(trim($name));
    if ($key === '') throw new InvalidArgumentException("Empty lookup name for $table");

    if (isset($cache->{$cacheProp}[$key])) return $cache->{$cacheProp}[$key];

    // try select
    $stmt = $pdo->prepare("SELECT `$idCol` AS id FROM `$table` WHERE `name` = :n LIMIT 1");
    $stmt->execute([':n' => $name]);
    $row = $stmt->fetch();
    if ($row) {
        $cache->{$cacheProp}[$key] = (int)$row['id'];
        return (int)$row['id'];
    }

    // insert
    $ins = $pdo->prepare("INSERT INTO `$table` (`name`) VALUES (:n)");
    $ins->execute([':n' => $name]);
    $id = (int)$pdo->lastInsertId();
    $cache->{$cacheProp}[$key] = $id;
    return $id;
}

function upsertInformant(PDO $pdo, array $r): void {
    $sql = <<<SQL
INSERT INTO informant (
  informant_id,
  last_name, first_name, maiden_name, title, nickname,
  cinneadh, sloinneadh_breithe, ainm, tiotal_ga, patronymic,
  gender, years_recorded,
  community_origin_canada, county, province_canada, country, tradition_scotland,
  dates_raw, biography_doc
) VALUES (
  :informant_id,
  :last_name, :first_name, :maiden_name, :title, :nickname,
  :cinneadh, :sloinneadh_breithe, :ainm, :tiotal_ga, :patronymic,
  :gender, :years_recorded,
  :community_origin_canada, :county, :province_canada, :country, :tradition_scotland,
  :dates_raw, :bio_doc
)
ON DUPLICATE KEY UPDATE
  last_name = COALESCE(VALUES(last_name), last_name),
  first_name = COALESCE(VALUES(first_name), first_name),
  maiden_name = COALESCE(VALUES(maiden_name), maiden_name),
  title = CASE WHEN title IS NULL OR title = '' THEN VALUES(title) ELSE title END,
  nickname = COALESCE(VALUES(nickname), nickname),
  cinneadh = COALESCE(VALUES(cinneadh), cinneadh),
  sloinneadh_breithe = COALESCE(VALUES(sloinneadh_breithe), sloinneadh_breithe),
  ainm = COALESCE(VALUES(ainm), ainm),
  tiotal_ga = COALESCE(VALUES(tiotal_ga), tiotal_ga),
  patronymic = COALESCE(VALUES(patronymic), patronymic),
  gender = COALESCE(VALUES(gender), gender),
  years_recorded = COALESCE(VALUES(years_recorded), years_recorded),
  community_origin_canada = COALESCE(VALUES(community_origin_canada), community_origin_canada),
  county = COALESCE(VALUES(county), county),
  province_canada = COALESCE(VALUES(province_canada), province_canada),
  country = COALESCE(VALUES(country), country),
  tradition_scotland = COALESCE(VALUES(tradition_scotland), tradition_scotland),
  dates_raw = COALESCE(VALUES(dates_raw), dates_raw),
  bio_doc = COALESCE(VALUES(bio_doc), bio_doc)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':informant_id' => $r['informant_id'],
        ':last_name' => $r['last_name'] ?? null,
        ':first_name' => $r['first_name'] ?? null,
        ':maiden_name' => $r['maiden_name'] ?? null,
        ':title' => $r['title'] ?? null,
        ':nickname' => $r['nickname'] ?? null,
        ':cinneadh' => $r['cinneadh'] ?? null,
        ':sloinneadh_breithe' => $r['sloinneadh_breithe'] ?? null,
        ':ainm' => $r['ainm'] ?? null,
        ':tiotal_ga' => $r['tiotal_ga'] ?? null,
        ':patronymic' => $r['patronymic'] ?? null,
        ':gender' => $r['gender'] ?? null,
        ':years_recorded' => $r['years_recorded'] ?? null,
        ':community_origin_canada' => $r['community_origin_canada'] ?? null,
        ':county' => $r['county'] ?? null,
        ':province_canada' => $r['province_canada'] ?? null,
        ':country' => $r['country'] ?? null,
        ':tradition_scotland' => $r['tradition_scotland'] ?? null,
        ':dates_raw' => $r['dates_raw'] ?? null,
        ':bio_doc' => $r['bio_doc'] ?? null,
    ]);
}

function upsertInformantImage(PDO $pdo, string $informantId, int $slot, ?string $filename, ?string $caption): void {
    if ($filename === null && $caption === null) return;

    $sql = <<<SQL
INSERT INTO informant_image (informant_id, slot, filename, caption)
VALUES (:iid, :slot, :fn, :cap)
ON DUPLICATE KEY UPDATE
  filename = COALESCE(VALUES(filename), filename),
  caption = COALESCE(VALUES(caption), caption)
SQL;
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':iid' => $informantId,
        ':slot' => $slot,
        ':fn' => $filename,
        ':cap' => $caption,
    ]);
}

function upsertComposer(PDO $pdo, array $r): void {
    $sql = <<<SQL
INSERT INTO composer (
  composer_id,
  last_name, first_name, title, nickname, maiden_name,
  cinneadh, sloinneadh_breithe, ainm, tiotal_ga, patronymic,
  dates_raw, gender,
  place_of_birth, location_community, location_county, tradition_scotland,
  biography_doc, image_filename
) VALUES (
  :composer_id,
  :last_name, :first_name, :title, :nickname, :maiden_name,
  :cinneadh, :sloinneadh_breithe, :ainm, :tiotal_ga, :patronymic,
  :dates_raw, :gender,
  :place_of_birth, :location_community, :location_county, :tradition_scotland,
  :biography_doc, :image_filename
)
ON DUPLICATE KEY UPDATE
  last_name = COALESCE(VALUES(last_name), last_name),
  first_name = COALESCE(VALUES(first_name), first_name),
  title = CASE WHEN title IS NULL OR title = '' THEN VALUES(title) ELSE title END,
  nickname = COALESCE(VALUES(nickname), nickname),
  maiden_name = COALESCE(VALUES(maiden_name), maiden_name),
  cinneadh = COALESCE(VALUES(cinneadh), cinneadh),
  sloinneadh_breithe = COALESCE(VALUES(sloinneadh_breithe), sloinneadh_breithe),
  ainm = COALESCE(VALUES(ainm), ainm),
  tiotal_ga = COALESCE(VALUES(tiotal_ga), tiotal_ga),
  patronymic = COALESCE(VALUES(patronymic), patronymic),
  dates_raw = COALESCE(VALUES(dates_raw), dates_raw),
  gender = COALESCE(VALUES(gender), gender),
  place_of_birth = COALESCE(VALUES(place_of_birth), place_of_birth),
  location_community = COALESCE(VALUES(location_community), location_community),
  location_county = COALESCE(VALUES(location_county), location_county),
  tradition_scotland = COALESCE(VALUES(tradition_scotland), tradition_scotland),
  biography_doc = COALESCE(VALUES(biography_doc), biography_doc),
  image_filename = COALESCE(VALUES(image_filename), image_filename)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':composer_id' => $r['composer_id'],
        ':last_name' => $r['last_name'] ?? null,
        ':first_name' => $r['first_name'] ?? null,
        ':title' => $r['title'] ?? null,
        ':nickname' => $r['nickname'] ?? null,
        ':maiden_name' => $r['maiden_name'] ?? null,
        ':cinneadh' => $r['cinneadh'] ?? null,
        ':sloinneadh_breithe' => $r['sloinneadh_breithe'] ?? null,
        ':ainm' => $r['ainm'] ?? null,
        ':tiotal_ga' => $r['tiotal_ga'] ?? null,
        ':patronymic' => $r['patronymic'] ?? null,
        ':dates_raw' => $r['dates_raw'] ?? null,
        ':gender' => $r['gender'] ?? null,
        ':place_of_birth' => $r['place_of_birth'] ?? null,
        ':location_community' => $r['location_community'] ?? null,
        ':location_county' => $r['location_county'] ?? null,
        ':tradition_scotland' => $r['tradition_scotland'] ?? null,
        ':biography_doc' => $r['biography_doc'] ?? null,
        ':image_filename' => $r['image_filename'] ?? null,
    ]);
}

function upsertRecording(PDO $pdo, array $r): void {
    $sql = <<<SQL
INSERT INTO recording (
  recording_id, informant_id, composer_id,
  title, alt_title, transcription_label,
  place_of_origin,
  genre_id, song_structure_id,
  song_air, first_line_chorus, first_line_verse,
  original_tape_no, original_tape_item_no,
  recording_date, includes_english_translation,
  notes1_additional_info, notes2_reference_sources, notes3_publications, notes4_team_notes
) VALUES (
  :recording_id, :informant_id, :composer_id,
  :title, :alt_title, :transcription_label,
  :place_of_origin,
  :genre_id, :song_structure_id,
  :song_air, :first_line_chorus, :first_line_verse,
  :original_tape_no, :original_tape_item_no,
  :recording_date, :includes_english_translation,
  :notes1, :notes2, :notes3, :notes4
)
ON DUPLICATE KEY UPDATE
  informant_id = VALUES(informant_id),
  composer_id = VALUES(composer_id),
  title = CASE WHEN title IS NULL OR title = '' THEN VALUES(title) ELSE title END,
  alt_title = COALESCE(VALUES(alt_title), alt_title),
  transcription_label = COALESCE(VALUES(transcription_label), transcription_label),
  place_of_origin = COALESCE(VALUES(place_of_origin), place_of_origin),
  genre_id = COALESCE(VALUES(genre_id), genre_id),
  song_structure_id = COALESCE(VALUES(song_structure_id), song_structure_id),
  song_air = COALESCE(VALUES(song_air), song_air),
  first_line_chorus = COALESCE(VALUES(first_line_chorus), first_line_chorus),
  first_line_verse = COALESCE(VALUES(first_line_verse), first_line_verse),
  original_tape_no = COALESCE(VALUES(original_tape_no), original_tape_no),
  original_tape_item_no = COALESCE(VALUES(original_tape_item_no), original_tape_item_no),
  recording_date = COALESCE(VALUES(recording_date), recording_date),
  includes_english_translation = COALESCE(VALUES(includes_english_translation), includes_english_translation),
  notes1_additional_info = COALESCE(VALUES(notes1_additional_info), notes1_additional_info),
  notes2_reference_sources = COALESCE(VALUES(notes2_reference_sources), notes2_reference_sources),
  notes3_publications = COALESCE(VALUES(notes3_publications), notes3_publications),
  notes4_team_notes = COALESCE(VALUES(notes4_team_notes), notes4_team_notes)
SQL;

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':recording_id' => $r['recording_id'],
        ':informant_id' => $r['informant_id'],
        ':composer_id' => $r['composer_id'] ?? null,
        ':title' => $r['title'] ?? null,
        ':alt_title' => $r['alt_title'] ?? null,
        ':transcription_label' => $r['transcription_label'] ?? null,
        ':place_of_origin' => $r['place_of_origin'] ?? null,
        ':genre_id' => $r['genre_id'] ?? null,
        ':song_structure_id' => $r['song_structure_id'] ?? null,
        ':song_air' => $r['song_air'] ?? null,
        ':first_line_chorus' => $r['first_line_chorus'] ?? null,
        ':first_line_verse' => $r['first_line_verse'] ?? null,
        ':original_tape_no' => $r['original_tape_no'] ?? null,
        ':original_tape_item_no' => $r['original_tape_item_no'] ?? null,
        ':recording_date' => $r['recording_date'] ?? null,
        ':includes_english_translation' => $r['includes_english_translation'] ?? 0,
        ':notes1' => $r['notes1'] ?? null,
        ':notes2' => $r['notes2'] ?? null,
        ':notes3' => $r['notes3'] ?? null,
        ':notes4' => $r['notes4'] ?? null,
    ]);
}

function linkRecordingMany(PDO $pdo, string $recId, string $jTable, string $colId, int $id): void {
    $sql = "INSERT IGNORE INTO `$jTable` (`recording_id`, `$colId`) VALUES (:rid, :id)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':rid' => $recId, ':id' => $id]);
}

/* -------------------- MAIN -------------------- */

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

$dryRun   = array_key_exists('dry-run', $o);
$truncate = array_key_exists('truncate', $o);

if (!is_file($file)) {
    fwrite(STDERR, "File not found: $file" . PHP_EOL);
    exit(1);
}

$ss = IOFactory::load($file);

// sheets (using the actual titles, but tolerant)
$wsRecordings = findSheet($ss, ['collection', 'collection recordings', 'recordings meta']);
$wsInformants = findSheet($ss, ['informant bios', 'informant_bios', 'informants']);
$wsComposers  = findSheet($ss, ['composer bios', 'composer_bios', 'composer', 'bard']);

if (!$wsRecordings || !$wsInformants || !$wsComposers) {
    fwrite(STDERR, "Could not find one or more required sheets.\n");
    fwrite(STDERR, "Found sheets: " . implode(', ', $ss->getSheetNames()) . PHP_EOL);
    exit(1);
}

$rowsInformants = readSheetRows($wsInformants);
$rowsComposers  = readSheetRows($wsComposers);
$rowsRecordings = readSheetRows($wsRecordings);

echo "Sheets read:\n";
echo "  Informants: " . count($rowsInformants) . "\n";
echo "  Composers:  " . count($rowsComposers) . "\n";
echo "  Recordings: " . count($rowsRecordings) . "\n\n";

if ($dryRun) {
    echo "[DRY RUN] No DB writes will be performed.\n\n";
}

$pdo = pdoConnect($dsn, $user, $pass);

if ($truncate && !$dryRun) {
    echo "Truncating target tables...\n";
    truncateAll($pdo);
    echo "Done.\n\n";
}

$cache = new LookupCache();

try {
    if (!$dryRun) $pdo->beginTransaction();

    /* -------- Import INFORMANTS -------- */
    $cntInf = 0;
    foreach ($rowsInformants as $r) {
        $informantId = $r['informant id'] ?? null;
        $informantId = $informantId ? trim($informantId) : null;
        if (!$informantId) continue;

        $payload = [
            'informant_id' => $informantId,
            'last_name' => firstNonEmpty($r, ['informant last name', 'last name']),
            'first_name' => $r['informant first name'] ?? null,
            'maiden_name' => $r['informant maiden name'] ?? null,
            'title' => $r['title'] ?? null,
            'nickname' => $r['nickname/familiar name'] ?? null,
            'cinneadh' => $r['cinneadh'] ?? null,
            'sloinneadh_breithe' => $r['sloinneadh-breithe'] ?? null,
            'ainm' => $r['ainm'] ?? null,
            'tiotal_ga' => $r['tiotal'] ?? null,
            'patronymic' => $r['sloinneadh/patronymic'] ?? null,
            'gender' => $r['gender'] ?? null,
            'years_recorded' => $r['year(s) recorded'] ?? null,
            'community_origin_canada' => $r["community of origin (canada)"] ?? null,
            'county' => $r['county'] ?? null,
            'province_canada' => $r['province (canada)'] ?? null,
            'country' => $r['country'] ?? null,
            'tradition_scotland' => $r['tradition (scotland)'] ?? null,
            'dates_raw' => $r['dates'] ?? null,
            'bio_doc' => $r['bio doc'] ?? null,
        ];

        if (!$dryRun) {
            upsertInformant($pdo, $payload);

            // images
            upsertInformantImage($pdo, $informantId, 1, $r['image .p1'] ?? null, $r['image .p1 caption'] ?? null);
            upsertInformantImage($pdo, $informantId, 2, $r['image .p2'] ?? null, $r['caption .p2'] ?? null);
            upsertInformantImage($pdo, $informantId, 3, $r['image .p3'] ?? null, $r['caption .p3'] ?? null);
        }
        $cntInf++;
    }
    echo "Imported informants: $cntInf\n";

    /* -------- Import COMPOSERS -------- */
    $cntComp = 0;
    foreach ($rowsComposers as $r) {
        $composerId = $r['bard id'] ?? null;
        $composerId = $composerId ? trim($composerId) : null;
        if (!$composerId) continue;

        $payload = [
            'composer_id' => $composerId,
            'last_name' => $r['bard last name'] ?? null,
            'first_name' => $r['bard first name'] ?? null,
            'title' => $r['title'] ?? null,
            'nickname' => $r['nickname/familiar name'] ?? null,
            'maiden_name' => $r['composer maiden name'] ?? null,
            'cinneadh' => $r['cinneadh'] ?? null,
            'sloinneadh_breithe' => $r['sloinneadh-breithe'] ?? null,
            'ainm' => $r['ainm'] ?? null,
            'tiotal_ga' => $r['tiotal'] ?? null,
            'patronymic' => $r['composer patronymic'] ?? null,
            'dates_raw' => $r['composer dates'] ?? null,
            'gender' => $r['composer gender'] ?? null,
            'place_of_birth' => $r['place of birth'] ?? null,
            'location_community' => $r['composer location - associated community'] ?? null,
            'location_county' => $r['composer location - associated county'] ?? null,
            'tradition_scotland' => $r['tradition (scotland)'] ?? null,
            'biography_doc' => $r['composer biography'] ?? null,
            'image_filename' => $r['composer image'] ?? null,
        ];

        if (!$dryRun) upsertComposer($pdo, $payload);
        $cntComp++;
    }
    echo "Imported composers: $cntComp\n";

    /* -------- Import RECORDINGS -------- */
    $cntRec = 0;
    $cntRecSub = 0;
    $cntRecSubject = 0;

    foreach ($rowsRecordings as $r) {
        $recordingId = $r['recording id'] ?? null;
        $recordingId = $recordingId ? trim($recordingId) : null;
        if (!$recordingId) continue;

        $informantId = $r['informant id'] ?? null;
        $informantId = $informantId ? trim($informantId) : null;
        if (!$informantId) {
            // recording row without informant id is not usable
            continue;
        }

        // Ensure informant exists (recording sheet contains minimal name fields)
        if (!$dryRun) {
            upsertInformant($pdo, [
                'informant_id' => $informantId,
                'last_name' => firstNonEmpty($r, ['informant last name', 'last name']),
                'first_name' => $r['informant first name'] ?? null,
            ]);
        }

        $composerId = $r['bard id'] ?? null; // note: "BARD ID#" becomes "bard id" after normalization
        if ($composerId !== null) $composerId = trim($composerId);
        if ($composerId === '') $composerId = null;

        $genreName = $r['genre'] ?? null;
        $structureName = firstNonEmpty($r, ['song structure (choose an option from the drop-down menu)', 'song structure']);

        $genreId = null;
        if (!$dryRun && $genreName) {
            $genreId = getOrCreateLookup($pdo, $cache, 'genre', $genreName, 'genre', 'genre_id');
        }

        $structureId = null;
        if (!$dryRun && $structureName) {
            $structureId = getOrCreateLookup($pdo, $cache, 'song_structure', $structureName, 'structure', 'structure_id');
        }

        $includesEn = $r['includes english translation'] ?? null;
        $includesEn = $includesEn ? trim($includesEn) : '';
        $includesEnBool = 0;
        if ($includesEn !== '') {
            $includesEnBool = in_array(mb_strtolower($includesEn), ['1', 'yes', 'y', 'true'], true) ? 1 : 0;
        }

        // TITLE column in spreadsheet is the recording title; TRANSCRIPTION is a separate field/label
        $transcription = $r['transcription'] ?? null;
        $altTitle = $r['alternative title'] ?? null;

        // Use TITLE as primary title (always populated in the sheet)
        $title = $r['title'] ?? null;

        $recordingDate = parseDateToYmd($r['recording date'] ?? null);

        $payload = [
            'recording_id' => $recordingId,
            'informant_id' => $informantId,
            'composer_id' => $composerId,
            'title' => $title,
            'alt_title' => $altTitle,
            'transcription_label' => $transcription,
            'place_of_origin' => $r['place of origin'] ?? null,
            'genre_id' => $genreId,
            'song_structure_id' => $structureId,
            'song_air' => $r['song air'] ?? null,
            'first_line_chorus' => $r['song first line (chorus)'] ?? null,
            'first_line_verse' => $r['song first line (verse)'] ?? null,
            'original_tape_no' => $r['original tape no.'] !== null ? (int)$r['original tape no.'] : null,
            'original_tape_item_no' => $r['original tape item no.'] ?? null,
            'recording_date' => $recordingDate,
            'includes_english_translation' => $includesEnBool,
            'notes1' => $r['notes 1 additional information from collection fieldnotes not included in metadata'] ?? null,
            'notes2' => $r['notes 2 reference source (published versions or articles that provide more context or actual lyrics/words to the song/story)'] ?? null,
            'notes3' => $r['notes 3 publications - transcripts made from this specific recording (e.g. sgeul gu latha, na beanntaichean gorma, brìgh an òrain, etc.)'] ?? null,
            'notes4' => $r['notes 4 additional notes from the language and lyrics team'] ?? null,
        ];

        if (!$dryRun) {
            upsertRecording($pdo, $payload);

            // link subgenres
            foreach (splitMulti($r['sub-genres'] ?? null) as $sg) {
                $sgId = getOrCreateLookup($pdo, $cache, 'subgenre', $sg, 'subgenre', 'subgenre_id');
                linkRecordingMany($pdo, $recordingId, 'recording_subgenre', 'subgenre_id', $sgId);
                $cntRecSub++;
            }

            // link subjects
            foreach (splitMulti($r['subjects'] ?? null) as $subj) {
                $subjId = getOrCreateLookup($pdo, $cache, 'subject', $subj, 'subject', 'subject_id');
                linkRecordingMany($pdo, $recordingId, 'recording_subject', 'subject_id', $subjId);
                $cntRecSubject++;
            }
        }

        $cntRec++;
    }

    echo "Imported recordings: $cntRec\n";
    echo "Linked recording_subgenre rows: $cntRecSub\n";
    echo "Linked recording_subject rows: $cntRecSubject\n";

    if (!$dryRun) {
        $pdo->commit();
        echo "\n✅ Import committed.\n";
    } else {
        echo "\n✅ Dry-run completed (no DB changes).\n";
    }

} catch (\Throwable $e) {
    if (!$dryRun && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "\n❌ Import failed: " . $e->getMessage() . "\n");
    exit(1);
}