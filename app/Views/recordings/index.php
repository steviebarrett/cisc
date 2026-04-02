<?php
$title = 'Recordings';

$activeNav = 'recordings';
$headerTitle = 'Recordings';
$bodyClass = 'page-recordings-list';
$fullWidth = true;

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

$kw = trim((string)($params['q'] ?? ''));
$place = trim((string)($params['place'] ?? ''));
$sort = (string)($params['sort'] ?? 'date_desc');
$perPage = (int)($params['per_page'] ?? 20);
$transcriptionQ = trim((string)($params['transcription_q'] ?? ''));

$selectedSubgenre = '';
if (!empty($params['subgenre']) && is_array($params['subgenre'])) {
    $selectedSubgenre = trim((string)$params['subgenre'][0]);
}

$selectedSubject = '';
if (!empty($params['subject']) && is_array($params['subject'])) {
    $selectedSubject = trim((string)$params['subject'][0]);
}

$hasEn = (int)($params['has_en'] ?? 0) === 1;
$hasTranscription = (int)($params['has_transcription'] ?? 0) === 1;

$genreAccentMap = [
    'story' => 'story',
    'song' => 'song',
    'belief' => 'belief',
    'biography' => 'biography',
    'custom' => 'custom',
    'proverb' => 'proverb',
];

$allSubgenresForFilter = array_values(array_filter(array_map(
    static fn($value) => trim((string)$value),
    $subgenres_all ?? []
), static fn($value) => $value !== ''));

$subgenresByGenreForFilter = is_array($subgenres_by_genre ?? null) ? $subgenres_by_genre : [];

$allSubgenresJson = json_encode($allSubgenresForFilter, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$subgenresByGenreJson = json_encode($subgenresByGenreForFilter, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="page-container">
    <h1 class="page-title">Claraidhean | Recordings</h1>

    <form method="get" class="card filter-panel">
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <input type="hidden" name="per_page" value="<?= $perPage ?>">
        <input type="hidden" name="page" value="1">

        <div class="filter-row">
            <div class="filter-field filter-field-grow">
                <input type="text" class="filter-input" name="q" value="<?= e($kw) ?>" placeholder="Search titles...">
            </div>
            <div class="filter-field filter-field-grow">
                <input type="text" class="filter-input" name="transcription_q" value="<?= e($transcriptionQ) ?>" placeholder="Search transcription content...">
            </div>
        </div>

        <div class="filter-row filter-row-selects">
            <div class="filter-field">
                <span class="filter-label">Aite | Place</span>
                <select class="filter-select filter-select-place js-searchable-select" name="place" data-placeholder="All places">
                    <option value="">All places</option>
                    <?php foreach (($places_all ?? []) as $p): ?>
                    <?php $placeValue = trim((string)$p); ?>
                    <option value="<?= e($placeValue) ?>" <?= $placeValue === $place ? 'selected' : '' ?>><?= e($placeValue) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <span class="filter-label">Seorsa | Genre</span>
                <select class="filter-select js-searchable-select" name="genre" data-placeholder="All genres">
                    <option value="">All</option>
                    <?php foreach (($genres ?? []) as $g): ?>
                    <?php $genreValue = trim((string)$g); ?>
                    <option value="<?= e($genreValue) ?>" <?= $genreValue === (string)($params['genre'] ?? '') ? 'selected' : '' ?>><?= e($genreValue) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <span class="filter-label">Fo-sheorsa | Sub-genre</span>
                <select class="filter-select js-searchable-select" name="subgenre[]" data-placeholder="All sub-genres">
                    <option value="">All</option>
                    <?php foreach (($subgenres_all ?? []) as $sg): ?>
                    <?php $subgenreValue = trim((string)$sg); ?>
                    <option value="<?= e($subgenreValue) ?>" <?= $subgenreValue === $selectedSubgenre ? 'selected' : '' ?>><?= e($subgenreValue) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-field">
                <span class="filter-label">Cuspair | Subject</span>
                <select class="filter-select js-searchable-select" name="subject" data-placeholder="All subjects">
                    <option value="">All</option>
                    <?php foreach (($subjects_all ?? []) as $s): ?>
                    <?php $subjectValue = trim((string)$s); ?>
                    <option value="<?= e($subjectValue) ?>" <?= $subjectValue === $selectedSubject ? 'selected' : '' ?>><?= e($subjectValue) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="filter-row-options">
            <div class="checkbox-group">
                <input type="checkbox" id="has-translation" name="has_en" value="1" <?= $hasEn ? 'checked' : '' ?>>
                <label for="has-translation">Has translation</label>
            </div>
            <div class="checkbox-group">
                <input type="checkbox" id="has-transcription" name="has_transcription" value="1" <?= $hasTranscription ? 'checked' : '' ?>>
                <label for="has-transcription">Has transcription</label>
            </div>
            <div class="filter-spacer"></div>
            <button type="submit" class="btn-apply">Apply</button>
            <a class="btn-reset" href="<?= e(base_path('/recordings')) ?>">Reset</a>
        </div>
    </form>

    <div class="results-bar">
        <span class="results-count"><?= number_format((int)($result['total'] ?? 0)) ?> results</span>

        <form method="get" class="results-controls">
            <input type="hidden" name="q" value="<?= e($kw) ?>">
            <input type="hidden" name="place" value="<?= e($place) ?>">
            <input type="hidden" name="genre" value="<?= e((string)($params['genre'] ?? '')) ?>">
            <input type="hidden" name="transcription_q" value="<?= e($transcriptionQ) ?>">
            <input type="hidden" name="has_en" value="<?= $hasEn ? '1' : '0' ?>">
            <input type="hidden" name="has_transcription" value="<?= $hasTranscription ? '1' : '0' ?>">
            <?php if ($selectedSubgenre !== ''): ?>
            <input type="hidden" name="subgenre[]" value="<?= e($selectedSubgenre) ?>">
            <?php endif; ?>
            <?php if ($selectedSubject !== ''): ?>
            <input type="hidden" name="subject" value="<?= e($selectedSubject) ?>">
            <?php endif; ?>
            <input type="hidden" name="page" value="1">

            <div class="results-control-group">
                <span class="results-control-label">Sort</span>
                <select class="results-select" name="sort" onchange="this.form.submit()">
                    <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Newest first</option>
                    <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Oldest first</option>
                    <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Title A-Z</option>
                    <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Title Z-A</option>
                </select>
            </div>

            <div class="results-control-group">
                <span class="results-control-label">Per page</span>
                <select class="results-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 20, 50, 100] as $n): ?>
                    <option value="<?= $n ?>" <?= $perPage === $n ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="recording-list">
        <?php foreach (($result['rows'] ?? []) as $row): ?>
        <?php
            $recId = trim((string)($row['recording_id'] ?? ''));
            $recUrl = base_path('/recordings/' . rawurlencode($recId));
            $genreLabel = trim((string)($row['genre_name'] ?? ''));

            $accent = 'story';
            $genreLower = mb_strtolower($genreLabel);
            foreach ($genreAccentMap as $needle => $suffix) {
                if ($genreLower !== '' && str_contains($genreLower, $needle)) {
                    $accent = $suffix;
                    break;
                }
            }

            $qs = clean_qs($_GET);
            if ($qs !== '') {
                $recUrl .= '?' . $qs;
            }
            ?>
        <div class="recording-card">
            <div class="recording-accent recording-accent-<?= e($accent) ?>"></div>
            <div class="recording-content">
                <div class="recording-title-row">
                    <a href="<?= e($recUrl) ?>" class="recording-title">
                        <?= $kw !== ''
                                ? highlight_ga(($row['title'] ?: $row['recording_id']), $kw)
                                : e(($row['title'] ?: $row['recording_id'])) ?>
                    </a>
                    <span class="recording-id"><?= e($recId) ?></span>
                </div>

                <div class="recording-meta">
                    <?= e(trim((string)($row['informant_name'] ?? ''))) ?>
                    <?php if ($genreLabel !== ''): ?> - <?= e($genreLabel) ?><?php endif; ?>
                    <?php if (!empty($row['includes_english_translation'])): ?> - EN<?php endif; ?>
                    <?php if (!empty($row['transcription_html'])): ?> - <i data-lucide="file-text" aria-label="Has transcription"></i><?php endif; ?>
                </div>

                <?php if ($transcriptionQ !== '' && !empty($row['transcription_text'])): ?>
                <div class="recording-meta">
                    <?= highlight_excerpt_ga((string)$row['transcription_text'], $transcriptionQ) ?>
                </div>
                <?php endif; ?>

                <?php if ($kw !== '' && mb_stristr((string)($row['inf_biography_text'] ?? ''), $kw) !== false): ?>
                <div class="recording-meta">
                    <strong><em>Informant Bio: </em></strong>
                    <?= highlight_excerpt_ga((string)$row['inf_biography_text'], $kw) ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($row['cmp_biography_text']) && $kw !== '' && mb_stristr((string)$row['cmp_biography_text'], $kw) !== false): ?>
                <div class="recording-meta">
                    <strong><em>Composer Bio: </em></strong>
                    <?= highlight_excerpt_ga((string)$row['cmp_biography_text'], $kw) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    const genreSelect = document.querySelector('select[name="genre"]');
    const subgenreSelect = document.querySelector('select[name="subgenre[]"]');
    if (!genreSelect || !subgenreSelect) return;

    const allSubgenres = <?= $allSubgenresJson ?: '[]' ?>;
    const subgenresByGenre = <?= $subgenresByGenreJson ?: '{}' ?>;

    const getAllowedSubgenres = (genreValue) => {
        const genre = (genreValue || '').trim();
        if (genre === '') return null;

        const mapped = subgenresByGenre[genre];
        if (!Array.isArray(mapped)) return new Set();
        return new Set(mapped);
    };

    const hasTomSelectInstances = () => Boolean(genreSelect.tomselect && subgenreSelect.tomselect);

    const applySubgenreFilter = () => {
        const genreTs = genreSelect.tomselect;
        const subgenreTs = subgenreSelect.tomselect;
        if (!genreTs || !subgenreTs) return;

        const selectedGenre = String(genreTs.getValue() || '').trim();
        const selectedSubgenre = String(subgenreTs.getValue() || '').trim();
        const allowed = getAllowedSubgenres(selectedGenre);

        subgenreTs.clearOptions();
        subgenreTs.addOption({
            value: '',
            text: 'All'
        });

        for (const subgenre of allSubgenres) {
            if (allowed && !allowed.has(subgenre)) continue;
            subgenreTs.addOption({
                value: subgenre,
                text: subgenre
            });
        }

        subgenreTs.refreshOptions(false);

        if (selectedSubgenre !== '' && (!allowed || allowed.has(selectedSubgenre))) {
            subgenreTs.setValue(selectedSubgenre, true);
        } else {
            subgenreTs.setValue('', true);
        }
    };

    const bindWhenReady = (attempt = 0) => {
        if (!hasTomSelectInstances()) {
            if (attempt < 20) {
                window.setTimeout(() => bindWhenReady(attempt + 1), 50);
            }
            return;
        }

        applySubgenreFilter();
        genreSelect.tomselect.on('change', applySubgenreFilter);
    };

    bindWhenReady();
});
</script>