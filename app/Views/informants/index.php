<?php
$title = 'Informants';
$activeNav = 'informants';
$headerTitle = 'Informants';
$bodyClass = 'page-informants-list';
$fullWidth = true;

$kw = trim((string)($params['q'] ?? ''));
$sort = (string)($params['sort'] ?? 'english_name_asc');
$perPage = (int)($params['per_page'] ?? 12);
?>

<!-- Sorting Notes:

English Name sort:

Primary: last_name
Secondary: first_name
Final tie-breaker: informant_id

Gaelic Name sort:
Primary: cinneadh
Secondary: ainm

If Gaelic fields are blank, it falls back to English fields, then informant_id

-->

<div class="page-container">
    <h1 class="page-title">Beulaichean | Informants</h1>

    <div class="controls-bar">
        <span class="controls-count"><?= number_format((int)($result['total'] ?? 0)) ?> informants</span>

        <form method="get" class="controls-right">
            <?php if ($kw !== ''): ?>
            <input type="hidden" name="q" value="<?= e($kw) ?>">
            <?php endif; ?>
            <input type="hidden" name="page" value="1">

            <div class="control-group">
                <span class="control-label">Sort</span>
                <select class="control-select" name="sort" onchange="this.form.submit()">
                    <option value="english_name_asc" <?= in_array($sort, ['english_name_asc', 'name_asc'], true) ? 'selected' : '' ?>>English Name (A–Z)</option>
                    <option value="english_name_desc" <?= in_array($sort, ['english_name_desc', 'name_desc'], true) ? 'selected' : '' ?>>English Name (Z–A)</option>
                    <option value="gaelic_name_asc" <?= $sort === 'gaelic_name_asc' ? 'selected' : '' ?>>Gaelic Name (A–Z)</option>
                    <option value="gaelic_name_desc" <?= $sort === 'gaelic_name_desc' ? 'selected' : '' ?>>Gaelic Name (Z–A)</option>
                </select>
            </div>

            <div class="control-group">
                <span class="control-label">Per page</span>
                <select class="control-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([12, 24, 48, 96] as $n): ?>
                    <option value="<?= $n ?>" <?= $perPage === $n ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="informant-grid">
        <?php foreach (($result['rows'] ?? []) as $row): ?>
        <?php
            $id = trim((string)($row['informant_id'] ?? ''));
            $url = base_path('/informants/' . rawurlencode($id));
            $name = trim((string)(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')));
            if ($name === '') $name = $id;

            $nameGa = trim((string)(($row['ainm'] ?? '') . ' ' . ($row['cinneadh'] ?? '')));
            $community = trim((string)($row['community_origin_canada'] ?? ''));
            $county = trim((string)($row['county'] ?? ''));
            $recordingCount = (int)($row['recording_count'] ?? 0);

            $imageFilename = trim((string)($row['image_filename'] ?? ''));
            $photoStyle = '';
            if ($imageFilename !== '') {
                $photoStyle = 'background-image: url(\'' . e(base_path('/media/informants/' . rawurlencode($imageFilename))) . '\')';
            }
            ?>
        <a class="informant-card" href="<?= e($url) ?>">
            <div class="informant-photo" <?= $photoStyle !== '' ? ' style="' . $photoStyle . '"' : '' ?>></div>

            <div class="informant-content">
                <div class="informant-name-en">
                    <?= $kw !== '' ? highlight_ga($name, $kw) : e($name) ?>
                </div>

                <?php if ($nameGa !== ''): ?>
                <div class="informant-name-gd"><?= $kw !== '' ? highlight_ga($nameGa, $kw) : e($nameGa) ?></div>
                <?php endif; ?>

                <?php if ($community !== ''): ?>
                <div class="informant-community"><?= $kw !== '' ? highlight_ga($community, $kw) : e($community) ?></div>
                <?php endif; ?>

                <div class="informant-bottom-row">
                    <span class="informant-county"><?= $county !== '' ? ($kw !== '' ? highlight_ga($county, $kw) : e($county)) : '&nbsp;' ?></span>
                    <span class="informant-rec-count"><?= number_format($recordingCount) ?> recs</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php
    $pages = (int)($result['pages'] ?? 1);
    $page = (int)($result['page'] ?? 1);
    ?>
    <?php if ($pages > 1): ?>
    <div class="pagination">
        <?php $prev = max(1, $page - 1); ?>
        <?php if ($page <= 1): ?>
        <span class="page-btn page-btn-disabled">Prev</span>
        <?php else: ?>
        <a class="page-btn page-btn-text" href="?<?= e(qs(['page' => $prev])) ?>">Prev</a>
        <?php endif; ?>

        <?php
            $start = max(1, $page - 3);
            $end = min($pages, $page + 3);
            for ($p = $start; $p <= $end; $p++):
            ?>
        <?php if ($p === $page): ?>
        <span class="page-btn page-btn-active"><?= $p ?></span>
        <?php else: ?>
        <a class="page-btn" href="?<?= e(qs(['page' => $p])) ?>"><?= $p ?></a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php $next = min($pages, $page + 1); ?>
        <?php if ($page >= $pages): ?>
        <span class="page-btn page-btn-disabled">Next</span>
        <?php else: ?>
        <a class="page-btn page-btn-text" href="?<?= e(qs(['page' => $next])) ?>">Next</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>