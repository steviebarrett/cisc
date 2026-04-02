<?php

if (empty($rec) || empty($rec['recording_id'])): ?>
    <div class="alert alert-danger">
        Recording data missing in view. Check RecordingController::show() / Recording::find().
    </div>
<?php endif;


$title = $rec['title'] ?: $rec['recording_id'];


$backUrl = base_path('/recordings');
$qs = clean_qs($_GET);
if ($qs !== '') {
    $backUrl .= '?' . $qs;
}
?>

<div class="mb-3">
    <a href="<?= e($backUrl) ?>">&larr; Back to list</a>
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
                    Download <?= e($recId . '.mp3') ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <dl class="row mb-0">

            <dd class="row mb-0">
            <!--dt class="col-sm-3">Recording ID</dt><dd class="col-sm-9"><?= e($rec['recording_id']) ?></dd-->
            <dt class="col-sm-3">Beulaiche | Informant</dt>
            <dd class="col-sm-9">
                <a href="<?= e(base_path('/informants/' . $rec['informant_id'])) ?>">
                    <?= e(trim(($rec['informant_first'] ?? '') . ' ' . ($rec['informant_last'] ?? ''))) ?>
                </a>
            </dd>

            <?php if (!empty($rec['composer_id'])): ?>
            <dt class="col-sm-3">Bàrd | Composer</dt>
            <dd class="col-sm-9">
                    <a href="<?= e(base_path('/composers/' . $rec['composer_id'])) ?>">
                        <?= e(trim(($rec['composer_first'] ?? '') . ' ' . ($rec['composer_last'] ?? ''))) ?>
                    </a>
            </dd>
            <?php endif; ?>

            <?php
                $origin = $rec['place_of_origin'] == 'Scotland' ? 'Alba | Scotland' : e($rec['place_of_origin']);
            ?>
            <dt class="col-sm-3">Àite tùsail | Place of origin</dt><dd class="col-sm-9"><?= $origin ?></dd>
            <dt class="col-sm-3">Seòrsa | Genre</dt><dd class="col-sm-9"><?= e($rec['genre_name'] ?? '') ?></dd>

            <?php if (!empty($rec['subgenres'])): ?>
                <dt class="col-sm-3">Fo-sheòrsachan | Sub-genres</dt>
                    <dd class="col-sm-9">
                        <?php foreach ($rec['subgenres'] as $sg): ?>
                            <a class="badge text-bg-secondary text-decoration-none" href="<?= e(base_path('/recordings?' . http_build_query(['subgenre' => [$sg]]))) ?>">
                                <?= e($sg) ?>
                            </a>
                    <?php endforeach; ?>
                    </dd>
                </dt>
            <?php endif; ?>

            <?php if (!empty($rec['subjects'])): ?>

                    <dt class="col-sm-3">Cùspairean | Subjects:</dt>
                    <dd class="col-sm-9">
                    <?php foreach ($rec['subjects'] as $s): ?>
                        <a class="badge text-bg-light border text-decoration-none" href="<?= e(base_path('/recordings?' . http_build_query(['subject' => [$s]]))) ?>">
                            <?= e($s) ?>
                        </a>
                    <?php endforeach; ?>
                    </dd>
            <?php endif; ?>

            <?php if (!empty($rec['structure_name'])): ?>
                <dt class="col-sm-3">Crùth òrain | Song form</dt><dd class="col-sm-9"><?= e($rec['structure_name']) ?></dd>
            <?php endif; ?>

            <?php if (!empty($rec['song_air'])): ?>
                <dt class="col-sm-3">Fonn an òrain | Song air</dt><dd class="col-sm-9"><?= e($rec['song_air'] ?? '') ?></dd>
            <?php endif; ?>

            <?php if (!empty($rec['includes_english_translation'])): ?>
                <dt class="col-sm-3">Eadar-theangachadh Beurla | English translation</dt>
                <dd class="col-sm-9"><?= !empty($rec['includes_english_translation']) ? 'Yes' : 'No' ?></dd>
            <?php endif; ?>

            <dt class="col-sm-3">Ceann-latha clàraidh | Date recorded:</dt><dd class="col-sm-9"><?= e($rec['recording_date'] ?? '') ?></dd>

            <?php if (!empty($rec['original_tape_no'])): ?>
                <dt class="col-sm-3">Àireamh an teip | Tape No:</dt>
                <dd class="col-sm-9"><?= $rec['original_tape_no'] .  ' ' . $rec['original_tape_item_no'] ?></dd>
            <?php endif; ?>

        </dl>

        <?php if (!empty($rec['first_line_chorus']) || !empty($rec['first_line_verse'])): ?>
            <hr>
            <h3 class="h5">First lines</h3>
            <?php if (!empty($rec['first_line_chorus'])): ?>
                <div>Chorus: <?= e($rec['first_line_chorus']) ?></div>
            <?php endif; ?>
            <?php if (!empty($rec['first_line_verse'])): ?>
                <div>Verse: <?= e($rec['first_line_verse']) ?></div>
            <?php endif; ?>
        <?php endif; ?>


        <hr>



        <!-- transcription -->
        <?php $transcriptionQ = trim((string)($_GET['transcription_q'] ?? ''));     //transcription query

        // highlight any searched for keyword in transcription context
        $transcriptionHtml = $transcriptionQ !== ''
                ? highlight_html_ga((string)$rec['transcription_html'], $transcriptionQ)
                : (string)$rec['transcription_html'];
        $transcriptionText = $rec['transcription_text'] ?? '';
        $hasTranscription  = (is_string($transcriptionHtml) && trim($transcriptionHtml) !== '')
                || (is_string($transcriptionText) && trim($transcriptionText) !== '');

        //check notes3 field and use if no transcription found
        $useNote = false;
        if (!$hasTranscription) {
            if (!empty($rec["notes3_publications"])) {
                $useNote = true;
                $transcriptionHtml = e($rec["notes3_publications"]);
            }
        }
        ?>

        <?php if ($hasTranscription || $useNote): ?>
            <hr>
            <details class="record-transcription" open>
                <summary><strong>Transcription</strong></summary>

                <?php if (trim($transcriptionHtml) !== ''): ?>
                    <?= $transcriptionHtml ?>
                <?php else: ?>
                    <?= nl2br(Functions::e_text($transcriptionText)) ?>
                <?php endif; ?>

                <?php if ($useNote === false): ?>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="/recordings/<?= rawurlencode((string)$rec['recording_id']) ?>/download-transcription">
                        Download transcription
                    </a>
                <?php endif; ?>
            </details>
        <?php endif; ?>

        <?php
        $notes = [
            'Notes (fieldnotes)' => $rec['notes1_additional_info'] ?? null,
            'Reference sources'  => $rec['notes2_reference_sources'] ?? null,
            //'Publications'       => $rec['notes3_publications'] ?? null,
            'Team notes'         => $rec['notes4_team_notes'] ?? null,
        ];
        $hasNotes = array_filter($notes, fn($x) => trim((string)$x) !== '');
        ?>
        <?php if ($hasNotes): ?>
            <hr>
            <div class="list-group">
                <?php foreach ($notes as $label => $txt):
                    $txt = trim((string)$txt);
                    if ($txt === '') continue;
                    ?>
                    <div class="list-group-item">
                        <strong><?= e($label) ?></strong>
                        <div class="mt-1">
                            <pre class="mb-0" style="white-space:pre-wrap;"><?= e($txt) ?></pre>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <!-- related recordings -->

        <?php if(!empty($relatedRecordings)):
            $informant = $relatedRecordings["same_informant"] ?? null;
            $subject = $relatedRecordings["same_subject"] ?? null;
            $genre = $relatedRecordings["same_genre"] ?? null;
        ?>

        <hr>

        <div class="mt-3">

            <h4>You might also like the following</h4>

            <ul class="featured-elements">
                <?php if(!empty($informant)): ?>
                    <li>
                        <a href="<?= e(base_path('/recordings/'.$informant["recording_id"]))?> ">
                            <h5>by <em><?= $informant['informant_name'] ?></em></h5>
                            <?= e($informant["recording_title"]) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(!empty($genre)): ?>
                    <li>
                        <h5>in the <em> <?= $genre['genre_name'] ?> </em> genre</h5>
                        <a href="<?= e(base_path('/recordings/'.$genre["recording_id"]))?> ">
                            <?= e($genre["recording_title"]) ?>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(!empty($subject)): ?>
                    <li>
                        <h5>with the subject <em><?= $subject['subject_name'] ?></em></h5>
                        <a href="<?= e(base_path('/recordings/'.$informant["recording_id"]))?> ">
                            <?= e($subject["recording_title"]) ?>
                        </a>
                    </li>
                <?php endif; ?>


            </ul>
        </div>
        <?php endif; ?>
    </div>

</div>