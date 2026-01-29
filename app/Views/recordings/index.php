<?php $title = 'Recordings';

// keyword
$kw = trim((string)($params['q'] ?? ''));

?>
<?php
$activeNav = 'recordings';
$headerTitle = 'Recordings';

// Determine whether filters are active (controls auto-open state)
$headerSearchOpen = false;
if ($kw !== '') $headerSearchOpen = true;
if (!empty($params['genre'])) $headerSearchOpen = true;
if (!empty($params['subgenres'])) $headerSearchOpen = true;
if (!empty($params['subjects'])) $headerSearchOpen = true;
if (!empty($params['has_en'])) $headerSearchOpen = true;
if (!empty($params['sort']) && $params['sort'] !== 'date_desc') $headerSearchOpen = true;
if (!empty($params['per_page']) && (int)$params['per_page'] !== 20) $headerSearchOpen = true;

// Build the search form body for the header
ob_start();
?>
<form method="get">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Keyword</label>
            <input class="form-control" name="q" value="<?= e($kw) ?>" placeholder="Title, first line, notes...">
        </div>

        <div class="col-12 col-lg-3">
            <label class="form-label">Genre</label>
            <select class="form-select" name="genre">
                <option value="">(Any)</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= $params['genre'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label class="form-label">Per page</label>
            <select class="form-select" name="per_page">
                <?php foreach ([10,20,50,100] as $n): ?>
                    <option value="<?= $n ?>" <?= ((int)$params['per_page'] === $n) ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-lg-1">
            <label class="form-label">Sort</label>
            <select class="form-select" name="sort">
                <option value="date_asc"  <?= $params['sort']==='date_asc' ? 'selected' : '' ?>>↑</option>
                <option value="date_desc" <?= $params['sort']==='date_desc' ? 'selected' : '' ?>>↓</option>
                <option value="title_asc" <?= $params['sort']==='title_asc' ? 'selected' : '' ?>>A→Z</option>
                <option value="title_desc"<?= $params['sort']==='title_desc' ? 'selected' : '' ?>>Z→A</option>
            </select>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_en" value="1" <?= ($params['has_en'] === 1) ? 'checked' : '' ?>>
                <label class="form-check-label">Includes English translation</label>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Sub-genres</label>
            <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                <?php $selected = $params['subgenres']; ?>
                <?php foreach ($subgenres_all as $sg): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subgenre[]" value="<?= e($sg) ?>" <?= in_array($sg, $selected, true) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= e($sg) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Subjects</label>
            <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                <?php $selected = $params['subjects']; ?>
                <?php foreach ($subjects_all as $s): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subject[]" value="<?= e($s) ?>" <?= in_array($s, $selected, true) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= e($s) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_path('/recordings')) ?>">Reset</a>
    </div>
</form>
<?php
$headerSearch = ob_get_clean();
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="text-muted">
            <?= (int)$result['total'] ?> results
        </div>
    </div>

    <div class="list-group">
        <?php foreach ($result['rows'] as $row): ?>
            <?php
            $recId = trim((string)($row['recording_id'] ?? ''));
            $recUrl = base_path('/recordings/' . rawurlencode($recId)) . "?q=" . rawurlencode($kw);
            ?>
            <a class="list-group-item list-group-item-action" href="<?= e($recUrl) ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-semibold">
                            <?= $kw !== ''
                                    ? highlight_ga($row['title'] ?: $row['recording_id'], $kw)
                                    : e($row['title'] ?: $row['recording_id']) ?>
                        </div>
                        <div class="small text-muted">
                            <?php if (!empty($row['alt_title'])): ?><em><?= highlight_ga($row['alt_title'], $kw) ?></em><br><?php endif; ?>
                            <?= e(trim(($row['informant_first'] ?? '') . ' ' . ($row['informant_last'] ?? ''))) ?>
                            <?php if (!empty($row['recording_date'])): ?> · <?= e($row['recording_date']) ?><?php endif; ?>
                            <?php if (!empty($row['genre_name'])): ?> · <?= e($row['genre_name']) ?><?php endif; ?>
                            <?php if (!empty($row['includes_english_translation'])): ?> · EN<?php endif; ?>
                        </div>

                        <?php if ($kw !== ''): ?>
                            <?php
                            $candidates = [
                                    $row['title'] ?? '',
                                    $row['alt_title'] ?? '',
                                    $row['first_line_chorus'] ?? '',
                                    $row['first_line_verse'] ?? '',
                                    $row['notes1_additional_info'] ?? '',
                            ];

                            $snippet = '';
                            foreach ($candidates as $t) {
                                if ($t !== '' && mb_stripos($t, $kw, 0, 'UTF-8') !== false) { $snippet = $t; break; }
                            }
                            ?>
                            <?php if ($snippet !== ''): ?>
                                <div class="mt-1 small text-muted">
                                    <?= highlight_excerpt_ga($snippet, $kw, 80) ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($row['subgenres'])): ?>
                            <div class="mt-1">
                                <?php foreach ($row['subgenres'] as $sg): ?>
                                    <span class="badge text-bg-secondary"><?= e($sg) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($row['subjects'])): ?>
                            <div class="mt-1">
                                <?php foreach ($row['subjects'] as $s): ?>
                                    <span class="badge text-bg-light border"><?= e($s) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-end small text-muted">
                        <?= e($recId) ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>