<?php
$params = $params ?? [];
$places_all = $places_all ?? [];
$genres = $genres ?? [];
$subgenres_all = $subgenres_all ?? [];
$subjects_all = $subjects_all ?? [];

if (isset($searchPanel) && is_array($searchPanel)) {
    if (empty($params) && isset($searchPanel['params']) && is_array($searchPanel['params'])) {
        $params = $searchPanel['params'];
    }
    if (empty($places_all) && isset($searchPanel['places_all']) && is_array($searchPanel['places_all'])) {
        $places_all = $searchPanel['places_all'];
    }
    if (empty($genres) && isset($searchPanel['genres']) && is_array($searchPanel['genres'])) {
        $genres = $searchPanel['genres'];
    }
    if (empty($subgenres_all) && isset($searchPanel['subgenres_all']) && is_array($searchPanel['subgenres_all'])) {
        $subgenres_all = $searchPanel['subgenres_all'];
    }
    if (empty($subjects_all) && isset($searchPanel['subjects_all']) && is_array($searchPanel['subjects_all'])) {
        $subjects_all = $searchPanel['subjects_all'];
    }
}

$places_all = !empty($places_all) ? $places_all : Taxonomy::places(1500);
$genres = !empty($genres) ? $genres : Taxonomy::genres();
$subgenres_all = !empty($subgenres_all) ? $subgenres_all : Taxonomy::subgenres();
$subjects_all = !empty($subjects_all) ? $subjects_all : Taxonomy::subjects();

$kw = isset($params['q']) && !is_array($params['q']) ? trim((string)$params['q']) : '';
$place = isset($params['place']) && !is_array($params['place']) ? trim((string)$params['place']) : '';
$transcriptionQ = trim((string)($params['transcription_q'] ?? ''));
$selectedGenre = trim((string)($params['genre'] ?? ''));

$hasEn = (int)($params['has_en'] ?? 0) === 1;
$hasTranscription = (int)($params['has_transcription'] ?? 0) === 1;

$selectedSubgenre = '';
if (!empty($params['subgenre']) && is_array($params['subgenre'])) {
    $selectedSubgenre = trim((string)$params['subgenre'][0]);
}

$selectedSubject = '';
if (!empty($params['subject'])) {
    if (is_array($params['subject'])) {
        $selectedSubject = trim((string)$params['subject'][0]);
    } else {
        $selectedSubject = trim((string)$params['subject']);
    }
}
?>

<div class="page-container global-search-shell">
    <form method="get" action="<?= e(base_path('/recordings')) ?>" class="global-search-panel" onsubmit="document.getElementById('search-closed-input').value='1';">
        <div class="filter-panel">
            <div class="filter-panel-header">
                <span class="filter-panel-title">Search & Filters</span>
            </div>

            <div class="filter-panel-body">
                <div class="filter-row">
                    <div class="filter-field filter-field-grow">
                        <input type="text" class="filter-input" name="q" value="<?= e($kw) ?>" placeholder="Search titles...">
                    </div>

                    <div class="filter-field filter-field-grow">
                        <input type="text" class="filter-input" name="transcription_q" id="transcription_q" value="<?= e($transcriptionQ) ?>"
                            placeholder="Search within transcriptions...">
                    </div>
                </div>

                <div class="filter-row filter-row-selects">
                    <div class="filter-field">
                        <span class="filter-label">Aite | Place</span>
                        <select class="filter-select filter-select-place js-searchable-select" name="place" data-placeholder="All places">
                            <option value="">All places</option>
                            <?php foreach ($places_all as $p): ?>
                            <?php $placeValue = trim((string)$p); ?>
                            <option value="<?= e($placeValue) ?>" <?= $placeValue === $place ? 'selected' : '' ?>><?= e($placeValue) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <span class="filter-label">Seorsa | Genre</span>
                        <select class="filter-select js-searchable-select" name="genre" data-placeholder="All genres">
                            <option value="">All</option>
                            <?php foreach ($genres as $g): ?>
                            <?php $genreValue = trim((string)$g); ?>
                            <option value="<?= e($genreValue) ?>" <?= $genreValue === $selectedGenre ? 'selected' : '' ?>><?= e($genreValue) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <span class="filter-label">Fo-sheorsa | Sub-genre</span>
                        <select class="filter-select js-searchable-select" name="subgenre[]" data-placeholder="All sub-genres">
                            <option value="">All</option>
                            <?php foreach ($subgenres_all as $sg): ?>
                            <?php $subgenreValue = trim((string)$sg); ?>
                            <option value="<?= e($subgenreValue) ?>" <?= $subgenreValue === $selectedSubgenre ? 'selected' : '' ?>><?= e($subgenreValue) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-field">
                        <span class="filter-label">Cuspair | Subject</span>
                        <select class="filter-select js-searchable-select" name="subject" data-placeholder="All subjects">
                            <option value="">All</option>
                            <?php foreach ($subjects_all as $s): ?>
                            <?php $subjectValue = trim((string)$s); ?>
                            <option value="<?= e($subjectValue) ?>" <?= $subjectValue === $selectedSubject ? 'selected' : '' ?>><?= e($subjectValue) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="search_closed" value="0" id="search-closed-input">

                <div class="filter-row-options">
                    <div class="checkbox-group">
                        <input type="checkbox" id="header-has-translation" name="has_en" value="1" <?= $hasEn ? 'checked' : '' ?>>
                        <label for="header-has-translation">Has translation</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="header-has-transcription" name="has_transcription" value="1" <?= $hasTranscription ? 'checked' : '' ?>>
                        <label for="header-has-transcription">Has transcription</label>
                    </div>
                    <div class="filter-spacer"></div>
                    <button class="btn-apply" type="submit">Apply</button>
                    <a class="btn-reset" href="<?= e(base_path('/recordings')) ?>">Reset</a>
                </div>

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

        $hasTranscription = (int)($params['has_transcription'] ?? 0);
        if ($hasTranscription === 1) {
            $active[] = [
                    'label' => 'Has transcription',
                    'qs'    => qs(['has_transcription' => 0, 'page' => 1]),
            ];
        }

        $transcriptionQ = trim((string)($params['transcription_q'] ?? ''));
        if ($transcriptionQ !== '') {
            $active[] = [
                    'label' => 'Transcription: ' . $transcriptionQ,
                    'qs'    => qs(['transcription_q' => '', 'page' => 1]),
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
        if (!is_array($subject)) {
            $subject = ((string)$subject !== '') ? [(string)$subject] : [];
        }
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
                <div class="filter-active">
                    <div class="filter-active-title">Active filters:</div>
                    <div class="filter-active-list">
                        <?php foreach ($active as $f): ?>
                        <span class="filter-active-chip">
                            <?= e($f['label']) ?>
                            <a href="?<?= e($f['qs']) ?>" title="Remove filter" aria-label="Remove filter: <?= e($f['label']) ?>">✕</a>
                        </span>
                        <?php endforeach; ?>

                        <a class="filter-active-clear" href="<?= e(base_path('/recordings')) ?>">
                            Clear all
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </form>
</div>
