<?php
$params = $params ?? [];
$places_all = $places_all ?? [];
$genres = $genres ?? [];

$subgenres_by_genre = $subgenres_by_genre ?? ($searchPanel['subgenres_by_genre'] ?? []);
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

<form method="get" action="<?= e(base_path('/recordings')) ?>" onsubmit="document.getElementById('search-closed-input').value='1';">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <label class="form-label">Keyword / title / first line</label>
            <input class="form-control" name="q" value="<?= e($kw) ?>" placeholder="Title, first line, notes...">
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label" for="transcription_q"><i class="fa-regular fa-file"></i> Transcription Content</label>
            <input class="form-control"
                   type="text"
                   name="transcription_q"
                   id="transcription_q"
                   value="<?= e((string)($params['transcription_q'] ?? '')) ?>"
                   placeholder="Search within transcriptions...">
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

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_en" value="1" <?= $hasEn === 1 ? 'checked' : '' ?>>
                <label class="form-check-label">English translation available</label>
            </div>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input"
                       type="checkbox"
                       name="has_transcription"
                       value="1"
                       id="has_transcription"
                    <?= $hasTranscription === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="has_transcription">
                    <i class="fa-regular fa-file"></i> Has transcription
                </label>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <label class="form-label">Genre</label>
            <select class="form-select" name="genre" id="genre">
                <option value="">(Any)</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= (($params['genre'] ?? '') === $g) ? 'selected' : '' ?>>
                        <?= e($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <script type="application/json" id="subgenres-by-genre-data">
            <?= json_encode($subgenres_by_genre, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        </script>

        <div id="subgenre-container">
            <?php foreach ($subgenres_all as $sg): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="subgenre[]" value="<?= e($sg) ?>" <?= in_array($sg, $selectedSubgenres, true) ? 'checked' : '' ?>>
                    <label class="form-check-label"><?= e($sg) ?></label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-12 col-lg-6">
            <label class="form-label">Subject</label>
            <input class="form-control"
                   list="subjectOptions"
                   name="subject"
                   value="<?= e($subjectValue) ?>"
                   placeholder="Start typing a subject...">

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

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_path('/recordings')) ?>">Reset</a>
    </div>

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

</form>


<!-- Javascript to dynamically update subgenre checkboxes based on genre selection -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const genreSelect = document.getElementById('genre');
        const subgenreContainer = document.getElementById('subgenre-container');
        const dataEl = document.getElementById('subgenres-by-genre-data');

        if (!genreSelect || !subgenreContainer || !dataEl) {
            return;
        }

        let subgenresByGenre = {};

        try {
            subgenresByGenre = JSON.parse(dataEl.textContent || '{}');
        } catch (err) {
            console.error('Could not parse subgenres-by-genre data', err);
            return;
        }

        function escapeHtml(str) {
            return String(str).replace(/[&<>"']/g, function (ch) {
                return ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                })[ch];
            });
        }

        function updateSubgenres() {
            const genre = genreSelect.value || '';

            const selectedValues = Array.from(
                subgenreContainer.querySelectorAll('input[name="subgenre[]"]:checked')
            ).map(input => input.value);

            const subgenres = genre && subgenresByGenre[genre]
                ? subgenresByGenre[genre]
                : [];

            subgenreContainer.innerHTML = '';

            if (!genre) {
                subgenreContainer.innerHTML = '<div class="text-muted small">Select a genre first</div>';
                return;
            }

            if (subgenres.length === 0) {
                subgenreContainer.innerHTML = '<div class="text-muted small">No subgenres available</div>';
                return;
            }

            for (const sg of subgenres) {
                const checked = selectedValues.includes(sg) ? ' checked' : '';

                subgenreContainer.insertAdjacentHTML('beforeend', `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="subgenre[]" value="${escapeHtml(sg)}"${checked}>
                    <label class="form-check-label">${escapeHtml(sg)}</label>
                </div>
            `);
            }
        }

        genreSelect.addEventListener('change', updateSubgenres);
        updateSubgenres();
    });
</script>