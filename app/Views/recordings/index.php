<?php
/** @var array $params */
/** @var array $result */
/** @var array $genres */
/** @var array $subgenres_all */
/** @var array $subjects_all */

$title = 'Recordings';

$kw = trim((string)($params['q'] ?? ''));
$place = trim((string)($params['place'] ?? ''));

$activeNav = 'recordings';
$headerTitle = 'Recordings';

$headerSearchOpen = header_filters_open($kw, $params, [
    'place'     => '',
    'genre'     => '',
    'subgenres' => [],
    'subjects'  => [],
    'has_en'    => 0,
    'sort'      => 'date_desc',
    'per_page'  => 20,
]);

ob_start();
?>
<form method="get">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Keyword</label>
            <input class="form-control" name="q" value="<?= e($kw) ?>" placeholder="Title, first line, notes...">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Place</label>
            <div class="input-group">
                <input class="form-control"
                       name="place"
                       value="<?= e($place) ?>"
                       placeholder="Start typing a place…"
                       list="placeOptions"
                       id="place-input">

                <button type="button"
                        class="btn btn-outline-secondary"
                        title="Clear place filter"
                        <?= $place === '' ? 'disabled' : '' ?>
                        onclick="document.getElementById('place-input').value=''; document.getElementById('place-input').focus();">
                    ✕
                </button>
            </div>

            <?php if (!empty($places_all)): ?>
                <datalist id="placeOptions">
                    <?php foreach ($places_all as $p): ?>
                        <option value="<?= e($p) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            <?php endif; ?>
        </div>

        <div class="col-12 col-lg-3">
            <label class="form-label">Genre</label>
            <select class="form-select" name="genre">
                <option value="">(Any)</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= (($params['genre'] ?? '') === $g) ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label class="form-label">Per page</label>
            <select class="form-select" name="per_page">
                <?php foreach ([10,20,50,100] as $n): ?>
                    <option value="<?= $n ?>" <?= ((int)($params['per_page'] ?? 20) === $n) ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-lg-1">
            <label class="form-label">Sort</label>
            <?php $sort = (string)($params['sort'] ?? 'date_desc'); ?>
            <select class="form-select" name="sort">
                <option value="date_asc"  <?= $sort==='date_asc' ? 'selected' : '' ?>>↑</option>
                <option value="date_desc" <?= $sort==='date_desc' ? 'selected' : '' ?>>↓</option>
                <option value="title_asc" <?= $sort==='title_asc' ? 'selected' : '' ?>>A→Z</option>
                <option value="title_desc"<?= $sort==='title_desc' ? 'selected' : '' ?>>Z→A</option>
            </select>
        </div>

        <div class="col-12">
            <div class="form-check">
                <?php $hasEn = (int)($params['has_en'] ?? 0); ?>
                <input class="form-check-input" type="checkbox" name="has_en" value="1" <?= ($hasEn === 1) ? 'checked' : '' ?>>
                <label class="form-check-label">Includes English translation</label>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Sub-genres</label>
            <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                <?php $selected = (array)($params['subgenre'] ?? ($params['subgenres'] ?? [])); ?>
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
                <?php $selected = (array)($params['subject'] ?? ($params['subjects'] ?? [])); ?>
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
        <div class="text-muted"><?= (int)($result['total'] ?? 0) ?> results</div>
    </div>

    <div class="list-group">
        <?php foreach (($result['rows'] ?? []) as $row): ?>
            <?php
            $recId = trim((string)($row['recording_id'] ?? ''));
            $recUrl = base_path('/recordings/' . rawurlencode($recId));
            if ($kw !== '') $recUrl .= '?q=' . rawurlencode($kw);
            ?>
            <a class="list-group-item list-group-item-action" href="<?= e($recUrl) ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-semibold">
                            <?= $kw !== ''
                                ? highlight_ga(($row['title'] ?: $row['recording_id']), $kw)
                                : e(($row['title'] ?: $row['recording_id'])) ?>
                        </div>
                        <div class="small text-muted">
                            <?= e(trim((string)($row['informant_name'] ?? ''))) ?>
                            <?php if (!empty($row['recording_date'])): ?> · <?= e((string)$row['recording_date']) ?><?php endif; ?>
                            <?php if (!empty($row['genre_name'])): ?> · <?= e((string)$row['genre_name']) ?><?php endif; ?>
                            <?php if (!empty($row['includes_english_translation'])): ?> · EN<?php endif; ?>
                        </div>
                    </div>
                    <div class="text-end small text-muted"><?= e($recId) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>
