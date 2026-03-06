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

            <?php
            $selected = (array)($params['subject'] ?? ($params['subjects'] ?? []));
            $value = implode(', ', $selected);
            ?>

            <input
                    class="form-control"
                    list="subjectOptions"
                    name="subject"
                    value="<?= e($value) ?>"
                    placeholder="Start typing a subject..."
            >

            <?php if (!empty($subjects_all)): ?>
                <datalist id="subjectOptions">
                    <?php foreach ($subjects_all as $s): ?>
                        <option value="<?= e($s) ?>"></option>
                    <?php endforeach; ?>
                </datalist>
            <?php endif; ?>

            <!--div class="form-text">
                Separate multiple subjects with commas
            </div-->
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

    <?php
    // Build a convenient local view of active filters
    $active = [];

    $kw = trim((string)($params['q'] ?? ''));
    if ($kw !== '') {
        $active[] = [
                'label' => 'Keyword: ' . $kw,
                'qs'    => qs(['q' => '', 'page' => 1]),
        ];
    }

    $place = trim((string)($params['place'] ?? ''));
    if ($place !== '') {
        $active[] = [
                'label' => 'Place: ' . $place,
                'qs'    => qs(['place' => '', 'page' => 1]),
        ];
    }

    $genre = trim((string)($params['genre'] ?? ''));
    if ($genre !== '') {
        $active[] = [
                'label' => 'Genre: ' . $genre,
                'qs'    => qs(['genre' => '', 'page' => 1]),
        ];
    }

    $hasEn = (int)($params['has_en'] ?? 0);
    if ($hasEn === 1) {
        $active[] = [
                'label' => 'Has English',
                'qs'    => qs(['has_en' => 0, 'page' => 1]),
        ];
    }

    // Arrays can come in as subgenre[] / subject[]
    $subgenre = $params['subgenre'] ?? [];
    if (!is_array($subgenre)) $subgenre = [];
    foreach ($subgenre as $sg) {
        $sg = (string)$sg;
        if ($sg === '') continue;

        $remaining = array_values(array_filter($subgenre, fn($x) => (string)$x !== $sg));
        $active[] = [
                'label' => 'Sub-genre: ' . $sg,
                'qs'    => qs(['subgenre' => $remaining, 'page' => 1]),
        ];
    }

    $subject = $params['subject'] ?? [];
    if (!is_array($subject)) $subject = [];
    foreach ($subject as $s) {
        $s = (string)$s;
        if ($s === '') continue;

        $remaining = array_values(array_filter($subject, fn($x) => (string)$x !== $s));
        $active[] = [
                'label' => 'Subject: ' . $s,
                'qs'    => qs(['subject' => $remaining, 'page' => 1]),
        ];
    }
    ?>

    <?php if (!empty($active)): ?>
        <div class="mb-2">
            <div class="small text-muted mb-1">Active filters:</div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($active as $f): ?>
                    <span class="badge text-bg-light border">
                    <?= e($f['label']) ?>
                    <a class="text-decoration-none ms-1"
                       href="?<?= e($f['qs']) ?>"
                       title="Remove filter"
                       aria-label="Remove filter: <?= e($f['label']) ?>">✕</a>
                </span>
                <?php endforeach; ?>

                <a class="btn btn-sm btn-outline-secondary ms-1"
                   href="<?= e(base_path('/recordings')) ?>">
                    Clear all
                </a>
            </div>
        </div>
    <?php endif; ?>

    <?php $sort = (string)($params['sort'] ?? 'date_desc'); ?>
    <?php $perPage = (int)($params['per_page'] ?? 20); ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div class="text-muted">
            <?= (int)($result['total'] ?? 0) ?> results
        </div>

        <form method="get" class="d-flex flex-wrap align-items-center gap-2">
            <input type="hidden" name="q" value="<?= e((string)($params['q'] ?? '')) ?>">
            <input type="hidden" name="place" value="<?= e((string)($params['place'] ?? '')) ?>">
            <input type="hidden" name="genre" value="<?= e((string)($params['genre'] ?? '')) ?>">
            <input type="hidden" name="has_en" value="<?= (int)($params['has_en'] ?? 0) ?>">

            <?php foreach ((array)($params['subgenre'] ?? []) as $sg): ?>
                <input type="hidden" name="subgenre[]" value="<?= e((string)$sg) ?>">
            <?php endforeach; ?>

            <?php foreach ((array)($params['subject'] ?? []) as $s): ?>
                <input type="hidden" name="subject[]" value="<?= e((string)$s) ?>">
            <?php endforeach; ?>

            <input type="hidden" name="page" value="1">

            <label class="small text-muted mb-0" for="sort-select">Sort</label>
            <select class="form-select form-select-sm w-auto" name="sort" id="sort-select" onchange="this.form.submit()">
                <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest first</option>
                <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Oldest first</option>
                <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A–Z</option>
                <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z–A</option>
            </select>

            <label class="small text-muted mb-0" for="per-page-select">Per page</label>
            <select class="form-select form-select-sm w-auto" name="per_page" id="per-page-select" onchange="this.form.submit()">
                <?php foreach ([10,20,50,100] as $n): ?>
                    <option value="<?= $n ?>" <?= $perPage === $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="list-group">
        <?php foreach (($result['rows'] ?? []) as $row): ?>
            <?php
            $recId = trim((string)($row['recording_id'] ?? ''));
            $recUrl = base_path('/recordings/' . rawurlencode($recId));

            $qs = clean_qs($_GET);
            if ($qs !== '') {
                $recUrl .= '?' . $qs;
            }

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
