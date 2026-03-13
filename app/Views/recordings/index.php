<?php

$title = 'Recordings';

$kw = trim((string)($params['q'] ?? ''));
$place = trim((string)($params['place'] ?? ''));

$activeNav = 'recordings';
$headerTitle = 'Recordings';

$searchClosed = (string)($_GET['search_closed'] ?? '') === '1';

$params = array_merge([
    'q' => '',
    'place' => '',
    'genre' => '',
    'subgenre' => [],
    'subject' => [],
    'has_transcription' => 0,
    'transcription_q' => '',
    'has_en' => 0,
    'sort' => 'date_desc',
    'page' => 1,
    'per_page' => 20,
], $params ?? []);
?>



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
            <input type="hidden" name="has_transcription" value="<?= (int)($params['has_transcription'] ?? 0) ?>">
            <input type="hidden" name="transcription_q" value="<?= e((string)($params['transcription_q'] ?? '')) ?>">

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

    <?php $transcriptionQ = trim((string)($params['transcription_q'] ?? '')); ?>
    <div class="list-group">
        <?php foreach (($result['rows'] ?? []) as $row): ?>
            <?php
            $recId = trim((string)($row['recording_id'] ?? ''));
            $recUrl = base_path('/recordings/' . rawurlencode($recId));

            $qs = clean_qs($_GET);
            if ($qs !== '') {
                $recUrl .= '?' . $qs;
            }

            $recTrans = (!empty($row["transcription_html"]) ? '<i class="fa-regular fa-file"></i>' : '');
            ?>
            <a class="list-group-item list-group-item-action" href="<?= e($recUrl) ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-semibold">
                            <?= $kw !== ''
                                ? highlight_ga(($row['title'] ?: $row['recording_id']), $kw)
                                : e(($row['title'] ?: $row['recording_id'])) ?>
                            <?= $recTrans ?>
                        </div>
                        <div class="small text-muted">
                            <?= e(trim((string)($row['informant_name'] ?? ''))) ?>
                            <?php if (!empty($row['genre_name'])): ?> · <?= e((string)$row['genre_name']) ?><?php endif; ?>
                            <?php if (!empty($row['includes_english_translation'])): ?> · EN<?php endif; ?>
                        </div>
                        <?php if ($transcriptionQ !== '' && !empty($row['transcription_text'])): ?>
                            <div class="small mt-1 text-muted">
                                <?= highlight_excerpt_ga((string)$row['transcription_text'], $transcriptionQ) ?>
                            </div>
                        <?php endif; ?>
                        <!-- highlight keyword in informant bio -->
                        <?php if ($kw !== '' && mb_stristr($row['inf_biography_text'], $kw) !== false): ?>
                            <div class="small mt-1 text-muted">
                                <strong><em>Informant Bio: </em></strong>
                                <?= highlight_excerpt_ga((string)$row['inf_biography_text'], $kw) ?>
                            </div>
                        <?php endif; ?>
                        <!-- highlight keyword in composer bio -->
                        <?php if (!empty($row['cmp_biography_text']) && $kw !== '' && mb_stristr($row['cmp_biography_text'], $kw) !== false): ?>
                            <div class="small mt-1 text-muted">
                                <strong><em>Composer Bio: </em></strong>
                                <?= highlight_excerpt_ga((string)$row['cmp_biography_text'], $kw) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end small text-muted"><?= e($recId) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>


<script>
    function closeHeaderSearch() {
        const el = document.getElementById('searchPanel');
        if (el) el.classList.remove('show');
    }
</script>
