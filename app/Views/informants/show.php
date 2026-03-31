<?php
$activeNav = 'informants';
$headerTitle = 'Informants';
$bodyClass = 'page-informant-detail';
$fullWidth = true;

$nameGaelic = trim((string)(($inf['ainm'] ?? '') . ' ' . ($inf['cinneadh'] ?? '')));
$nameEnglish = trim((string)(($inf['first_name'] ?? '') . ' ' . ($inf['last_name'] ?? '')));
$nameDisplay = $nameEnglish !== '' ? $nameEnglish : trim((string)($inf['informant_id'] ?? ''));

$title = $nameDisplay;
if ($nameGaelic !== '') {
    $title = $nameGaelic . ' | ' . $title;
}

$backUrl = base_path('/informants');

$primaryImageUrl = '';
if (!empty($inf['images']) && is_array($inf['images'])) {
    $firstImage = $inf['images'][0]['filename'] ?? '';
    if ($firstImage !== '') {
        $primaryImageUrl = base_path('/media/informants/' . rawurlencode((string)$firstImage));
    }
}

$q = trim((string)($_GET['q'] ?? ''));
$biographyHtml = $q !== ''
    ? highlight_html_ga((string)($inf['biography_html'] ?? ''), $q)
    : (string)($inf['biography_html'] ?? '');

$recordingCount = is_array($recs ?? null) ? count($recs) : 0;

$genreClassMap = [
    'song' => 'genre-song',
    'story' => 'genre-story',
    'biography' => 'genre-biography',
    'custom' => 'genre-custom',
];
?>

<div class="page-container">
    <a href="<?= e($backUrl) ?>" class="back-link"><i class="fa-solid fa-arrow-left icon-sm" aria-hidden="true"></i> Informants</a>

    <div class="profile-header">
        <div class="profile-photo-column">
            <?php if ($primaryImageUrl !== ''): ?>
            <img src="<?= e($primaryImageUrl) ?>" alt="<?= e($nameDisplay) ?>" class="profile-photo" loading="lazy">
            <?php else: ?>
            <div class="profile-photo" style="background: var(--color-placeholder);"></div>
            <?php endif; ?>
        </div>

        <div class="profile-info-column">
            <div class="name-block">
                <?php if ($nameGaelic !== ''): ?>
                <h1 class="name-gaelic"><?= e($nameGaelic) ?></h1>
                <?php endif; ?>
                <p class="name-english"><?= e($nameDisplay) ?></p>
            </div>

            <div class="metadata-pairs">
                <?php if (!empty($inf['patronymic'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Sloinneadh | Patronymic</span>
                    <span class="metadata-value"><?= e(trim((string)$inf['patronymic'])) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($inf['maiden_name'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Ainm-baistidh | Maiden name</span>
                    <span class="metadata-value"><?= e(trim((string)$inf['maiden_name'])) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($inf['community_origin_canada'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Coimhearsnachd | Community</span>
                    <span class="metadata-value"><?= e(trim((string)$inf['community_origin_canada'])) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($inf['county'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Siorrachd | County</span>
                    <span class="metadata-value"><?= e(trim((string)$inf['county'])) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($inf['tradition_scotland'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Dualchas | Tradition (Scotland)</span>
                    <span class="metadata-value"><?= e(trim((string)$inf['tradition_scotland'])) ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($inf['dates_raw'])): ?>
                <div class="metadata-pair">
                    <span class="metadata-label">Cinn-latha | Dates</span>
                    <span class="metadata-value"><?= e((string)$inf['dates_raw']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="recording-count-badge">
                <span class="badge-pill"><?= number_format($recordingCount) ?> recordings</span>
            </div>
        </div>
    </div>

    <?php if (trim($biographyHtml) !== ''): ?>
    <div class="biography-section">
        <h2 class="section-heading">Eachdraidh-beatha | Biography</h2>
        <div class="biography-text">
            <?= $biographyHtml ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($recs)): ?>
    <div class="recordings-section">
        <h2 class="section-heading">Claraidhean | Recordings (<?= number_format($recordingCount) ?>)</h2>
        <div class="recording-list">
            <?php foreach ($recs as $r): ?>
            <?php
                    $recId = trim((string)($r['recording_id'] ?? ''));
                    $recTitle = trim((string)($r['title'] ?? ''));
                    if ($recTitle === '') {
                        $recTitle = $recId;
                    }
                    $genreName = trim((string)($r['genre_name'] ?? ''));
                    $genreLower = mb_strtolower($genreName);
                    $genreClass = '';
                    foreach ($genreClassMap as $needle => $className) {
                        if ($genreLower !== '' && str_contains($genreLower, $needle)) {
                            $genreClass = $className;
                            break;
                        }
                    }
                    $recUrl = base_path('/recordings/' . rawurlencode($recId));
                    $meta = trim((string)($r['recording_date'] ?? ''));
                    if ($genreName !== '') {
                        $meta .= ($meta !== '' ? ' · ' : '') . $genreName;
                    }
                    ?>
            <div class="recording-item <?= e($genreClass) ?>">
                <div class="recording-content">
                    <div class="recording-info">
                        <a href="<?= e($recUrl) ?>" class="recording-title"><?= e($recTitle) ?></a>
                        <?php if ($meta !== ''): ?>
                        <span class="recording-meta"><?= e($meta) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="recording-id"><?= e($recId) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>