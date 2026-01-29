<?php $title = $rec['title'] ?: $rec['recording_id']; ?>

<div class="mb-3">
    <a href="<?= e(base_path('/recordings')) . "?q=" . rawurlencode($_GET["q"])  ?>">&larr; Back to list</a>
</div>

<div class="card">
    <div class="card-body">
        <h2 class="h4 mb-1"><?= e($rec['title'] ?: $rec['recording_id']) ?></h2>
        <?php if (!empty($rec['alt_title'])): ?>
        <div class="text-muted mb-2">Alt: <em><?= e($rec['alt_title']) ?></em></div>
        <?php endif; ?>

        <?php
        // Audio player (expects files/audio/mp3/{recording_id}.mp3)
        $recId = trim((string)($rec['recording_id'] ?? ''));
        $audioDiskPath = rtrim(MP3_AUDIO_PATH, '/') . '/' . $recId . '.mp3';
        $hasAudio = ($recId !== '') && is_file($audioDiskPath);
        $audioUrl = base_path(MP3_AUDIO_URL . '/' . rawurlencode($recId) . '.mp3');
        $audioDownloadUrl = $audioUrl . '?download=1';
        ?>

        <?php if ($hasAudio): ?>
        <div class="mb-3">
            <audio class="w-100" controls preload="none">
                <source src="<?= e($audioUrl) ?>" type="audio/mpeg">
                Your browser does not support the audio element.
            </audio>
            <div class="mt-2">
                <a class="btn btn-sm btn-outline-secondary" href="<?= e($audioDownloadUrl) ?>" download="<?= e($recId . '.mp3') ?>">
                    Download song
                </a>
            </div>
            <div class="small text-muted mt-1"><?= e($recId) ?>.mp3</div>
        </div>
        <?php endif; ?>

        <dl class="row mb-0">
            <dt class="col-sm-3">Recording ID</dt><dd class="col-sm-9"><?= e($rec['recording_id']) ?></dd>
            <dt class="col-sm-3">Informant</dt>
            <dd class="col-sm-9">
                <a href="<?= e(base_path('/informants/' . $rec['informant_id'])) ?>">
                    <?= e(trim(($rec['informant_first'] ?? '') . ' ' . ($rec['informant_last'] ?? ''))) ?>
                </a>
            </dd>

            <dt class="col-sm-3">Composer</dt>
            <dd class="col-sm-9">
                <?php if (!empty($rec['composer_id'])): ?>
                    <a href="<?= e(base_path('/composers/' . $rec['composer_id'])) ?>">
                        <?= e(trim(($rec['composer_first'] ?? '') . ' ' . ($rec['composer_last'] ?? ''))) ?>
                    </a>
                <?php else: ?>
                    <span class="text-muted">(none)</span>
                <?php endif; ?>
            </dd>

            <dt class="col-sm-3">Date</dt><dd class="col-sm-9"><?= e($rec['recording_date'] ?? '') ?></dd>
            <dt class="col-sm-3">Place of origin</dt><dd class="col-sm-9"><?= e($rec['place_of_origin'] ?? '') ?></dd>
            <dt class="col-sm-3">Genre</dt><dd class="col-sm-9"><?= e($rec['genre_name'] ?? '') ?></dd>
            <dt class="col-sm-3">Structure</dt><dd class="col-sm-9"><?= e($rec['structure_name'] ?? '') ?></dd>
            <dt class="col-sm-3">Song air</dt><dd class="col-sm-9"><?= e($rec['song_air'] ?? '') ?></dd>
            <dt class="col-sm-3">English translation</dt>
            <dd class="col-sm-9"><?= !empty($rec['includes_english_translation']) ? 'Yes' : 'No' ?></dd>
        </dl>

        <?php if (!empty($rec['first_line_chorus']) || !empty($rec['first_line_verse'])): ?>
            <hr>
            <h3 class="h6">First lines</h3>
            <?php if (!empty($rec['first_line_chorus'])): ?>
                <div><strong>Chorus:</strong> <?= e($rec['first_line_chorus']) ?></div>
            <?php endif; ?>
            <?php if (!empty($rec['first_line_verse'])): ?>
                <div><strong>Verse:</strong> <?= e($rec['first_line_verse']) ?></div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($rec['subgenres']) || !empty($rec['subjects'])): ?>
            <hr>
            <?php if (!empty($rec['subgenres'])): ?>
                <div class="mb-2">
                    <strong>Sub-genres:</strong>
                    <?php foreach ($rec['subgenres'] as $sg): ?>
                        <a class="badge text-bg-secondary text-decoration-none" href="<?= e(base_path('/recordings?' . http_build_query(['subgenre' => [$sg]]))) ?>">
                            <?= e($sg) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($rec['subjects'])): ?>
                <div>
                    <strong>Subjects:</strong>
                    <?php foreach ($rec['subjects'] as $s): ?>
                        <a class="badge text-bg-light border text-decoration-none" href="<?= e(base_path('/recordings?' . http_build_query(['subject' => [$s]]))) ?>">
                            <?= e($s) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        $notes = [
            'Notes (fieldnotes)' => $rec['notes1_additional_info'] ?? null,
            'Reference sources'  => $rec['notes2_reference_sources'] ?? null,
            'Publications'       => $rec['notes3_publications'] ?? null,
            'Team notes'         => $rec['notes4_team_notes'] ?? null,
        ];
        $hasNotes = array_filter($notes, fn($x) => trim((string)$x) !== '');
        ?>
        <?php if ($hasNotes): ?>
            <hr>
            <div class="accordion" id="notesAcc">
                <?php $i=0; foreach ($notes as $label => $txt): $txt = trim((string)$txt); if ($txt==='') continue; $i++; ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="h<?= $i ?>">
                            <button class="accordion-button <?= $i===1 ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#c<?= $i ?>">
                                <?= e($label) ?>
                            </button>
                        </h2>
                        <div id="c<?= $i ?>" class="accordion-collapse collapse <?= $i===1 ? 'show' : '' ?>" data-bs-parent="#notesAcc">
                            <div class="accordion-body"><pre class="mb-0" style="white-space:pre-wrap;"><?= e($txt) ?></pre></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>