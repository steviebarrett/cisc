<?php
$params = $params ?? [];
$places_all = $places_all ?? [];
$genres = $genres ?? [];
$subgenres_all = $subgenres_all ?? [];
$subjects_all = $subjects_all ?? [];

$kw = isset($params['q']) && !is_array($params['q']) ? trim((string)$params['q']) : '';
$place = isset($params['place']) && !is_array($params['place']) ? trim((string)$params['place']) : '';

$hasEn = (int)($params['has_en'] ?? 0);
$hasTranscription = (int)($params['has_transcription'] ?? 0);
$selectedSubgenres = (array)($params['subgenre'] ?? ($params['subgenres'] ?? []));
$subjectValue = is_array($params['subject'] ?? null)
    ? implode(', ', $params['subject'])
    : (string)($params['subject'] ?? '');
?>

<div class="page-container global-search-shell">
    <form method="get" action="<?= e(base_path('/recordings')) ?>" class="global-search-panel" onsubmit="document.getElementById('search-closed-input').value='1';">
        <div class="filter-panel">
            <div class="filter-panel-header">
                <span class="filter-panel-title">Search & Filters</span>
            </div>

            <div class="filter-panel-body">
                <div class="filter-row">
                    <div class="filter-field">
                        <span class="filter-label">Keyword / title / first line</span>
                        <input type="text" class="filter-input" name="q" value="<?= e($kw) ?>" placeholder="Title, first line, notes...">
                    </div>

                    <div class="filter-field">
                        <span class="filter-label">Transcription Content</span>
                        <input type="text" class="filter-input" name="transcription_q" id="transcription_q" value="<?= e((string)($params['transcription_q'] ?? '')) ?>"
                            placeholder="Search within transcriptions...">
                    </div>
                </div>

                <div class="filter-row">
                    <div class="filter-field">
                        <span class="filter-label">Place</span>
                        <div class="filter-input-wrapper">
                            <input type="text" class="filter-input" name="place" value="<?= e($place) ?>" placeholder="Start typing a place..." list="placeOptions"
                                id="place-input">
                            <button class="filter-clear-btn" type="button" title="Clear place filter" <?= $place === '' ? 'disabled' : '' ?>
                                onclick="document.getElementById('place-input').value=''; document.getElementById('place-input').focus();">
                                <i class="fa-solid fa-xmark icon-sm" aria-hidden="true"></i>
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

                    <div class="filter-field filter-field--auto">
                        <span class="filter-label">Genre</span>
                        <select class="filter-select" name="genre">
                            <option value="">(Any)</option>
                            <?php foreach ($genres as $g): ?>
                            <option value="<?= e($g) ?>" <?= (($params['genre'] ?? '') === $g) ? 'selected' : '' ?>><?= e($g) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="filter-checkboxes">
                    <label class="filter-checkbox">
                        <input type="checkbox" name="has_en" value="1" <?= $hasEn === 1 ? 'checked' : '' ?>>
                        <span class="filter-checkbox-label">Includes English translation</span>
                    </label>
                    <label class="filter-checkbox">
                        <input type="checkbox" name="has_transcription" value="1" <?= $hasTranscription === 1 ? 'checked' : '' ?>>
                        <span class="filter-checkbox-label">Has transcription</span>
                    </label>
                </div>

                <div class="filter-row">
                    <div class="filter-field">
                        <span class="filter-label">Sub-genres</span>
                        <div class="filter-checklist">
                            <?php foreach ($subgenres_all as $sg): ?>
                            <label class="filter-checklist-item">
                                <input type="checkbox" name="subgenre[]" value="<?= e($sg) ?>" <?= in_array($sg, $selectedSubgenres, true) ? 'checked' : '' ?>>
                                <span><?= e($sg) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="filter-field">
                        <span class="filter-label">Subject</span>
                        <input type="text" class="filter-input" list="subjectOptions" name="subject" value="<?= e($subjectValue) ?>" placeholder="Start typing a subject...">

                        <?php if (!empty($subjects_all)): ?>
                        <datalist id="subjectOptions">
                            <?php foreach ($subjects_all as $s): ?>
                            <option value="<?= e($s) ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <?php endif; ?>
                    </div>
                </div>

                <input type="hidden" name="search_closed" value="0" id="search-closed-input">

                <div class="filter-actions">
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