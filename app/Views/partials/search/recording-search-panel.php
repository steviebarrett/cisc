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

        <div class="col-12 col-lg-3">
            <label class="form-label">Genre</label>
            <select class="form-select" name="genre">
                <option value="">(Any)</option>
                <?php foreach ($genres as $g): ?>
                    <option value="<?= e($g) ?>" <?= (($params['genre'] ?? '') === $g) ? 'selected' : '' ?>>
                        <?= e($g) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="has_en" value="1" <?= $hasEn === 1 ? 'checked' : '' ?>>
                <label class="form-check-label">Includes English translation</label>
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

        <div class="col-12 col-lg-6">
            <label class="form-label">Sub-genres</label>
            <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                <?php foreach ($subgenres_all as $sg): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subgenre[]" value="<?= e($sg) ?>" <?= in_array($sg, $selectedSubgenres, true) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= e($sg) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
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
</form>