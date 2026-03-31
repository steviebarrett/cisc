<?php
if (empty($rec) || empty($rec['recording_id'])): ?>
<div class="alert alert-danger">
    Recording data missing in view. Check RecordingController::show() / Recording::find().
</div>
<?php
    return;
endif;

$title = $rec['title'] ?: $rec['recording_id'];
$bodyClass = 'page-recording-detail';
$fullWidth = true;

$recId = trim((string)($rec['recording_id'] ?? ''));
$recordingTitle = (string)($rec['title'] ?: $recId);
$genreName = trim((string)($rec['genre_name'] ?? ''));
$genreLower = mb_strtolower($genreName);
$genreTagClass = 'tag-story';
if (str_contains($genreLower, 'song')) $genreTagClass = 'tag-song';
if (str_contains($genreLower, 'belief')) $genreTagClass = 'tag-belief';
if (str_contains($genreLower, 'custom')) $genreTagClass = 'tag-custom';
if (str_contains($genreLower, 'biography')) $genreTagClass = 'tag-biography';
if (str_contains($genreLower, 'proverb')) $genreTagClass = 'tag-proverb';

$backUrl = base_path('/recordings');
$qs = clean_qs($_GET);
if ($qs !== '') {
    $backUrl .= '?' . $qs;
}

$audioDiskPath = rtrim(MP3_AUDIO_PATH, '/') . '/' . $recId . '.mp3';
$hasAudio = ($recId !== '') && is_file($audioDiskPath);
$audioUrl = base_path(MP3_AUDIO_URL . '/' . rawurlencode($recId) . '.mp3');
$audioDownloadUrl = $audioUrl . '?download=1';

$informantId = trim((string)($rec['informant_id'] ?? ''));
$informantName = trim((string)(($rec['informant_first'] ?? '') . ' ' . ($rec['informant_last'] ?? '')));
$informantUrl = $informantId !== '' ? base_path('/informants/' . rawurlencode($informantId)) : '';
$informantImageFilename = trim((string)($rec['informant_image_filename'] ?? ''));
$informantImageUrl = $informantImageFilename !== ''
    ? base_path('/media/informants/' . rawurlencode($informantImageFilename))
    : '';
$informantDetailLight = trim((string)($rec['informant_detail_light'] ?? ''));
$informantRecordingCount = (int)($rec['informant_recording_count'] ?? 0);
$composerId = trim((string)($rec['composer_id'] ?? ''));
$composerName = trim((string)(($rec['composer_first'] ?? '') . ' ' . ($rec['composer_last'] ?? '')));
$composerUrl = $composerId !== '' ? base_path('/composers/' . rawurlencode($composerId)) : '';

$origin = trim((string)($rec['place_of_origin'] ?? ''));
if ($origin === 'Scotland') {
    $origin = 'Alba | Scotland';
}

$transcriptionQ = trim((string)($_GET['transcription_q'] ?? ''));
$transcriptionHtml = $transcriptionQ !== ''
    ? highlight_html_ga((string)($rec['transcription_html'] ?? ''), $transcriptionQ)
    : (string)($rec['transcription_html'] ?? '');
$transcriptionText = (string)($rec['transcription_text'] ?? '');
$hasTranscription = (trim($transcriptionHtml) !== '') || (trim($transcriptionText) !== '');

$usePublicationNote = false;
if (!$hasTranscription && !empty($rec['notes3_publications'])) {
    $usePublicationNote = true;
    $transcriptionHtml = nl2br(e((string)$rec['notes3_publications']));
}

$notes = [
    'Notes (fieldnotes)' => $rec['notes1_additional_info'] ?? null,
    'Reference sources'  => $rec['notes2_reference_sources'] ?? null,
    'Team notes'         => $rec['notes4_team_notes'] ?? null,
];
$hasNotes = array_filter($notes, fn($x) => trim((string)$x) !== '');
$relatedRecords = is_array($relatedRecords ?? null) ? $relatedRecords : [];
?>

<!-- TODO/Notes:
 
1. Waveform is decorative, not audio-driven.
Current bars are generated in PHP loop SVG at show.php:81, so it does not reflect the real MP3 waveform.

We do not have the /files or /media directory in our project so I cant test informant photo nor mp3 functionality

-->

<div class="page-container">
    <a href="<?= e($backUrl) ?>" class="back-link"><i class="fa-solid fa-arrow-left icon-sm" aria-hidden="true"></i> Back to recordings</a>

    <div class="recording-header">
        <h1 class="recording-title"><?= e($recordingTitle) ?></h1>
        <?php if ($genreName !== ''): ?>
        <span class="tag <?= e($genreTagClass) ?>"><?= e($genreName) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($hasAudio): ?>
    <div class="audio-player">
        <div class="waveform-area" aria-hidden="true">
            <svg viewBox="0 0 800 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="waveGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#423EB2" stop-opacity="0.6" />
                        <stop offset="100%" stop-color="#1E99A3" stop-opacity="0.6" />
                    </linearGradient>
                </defs>
                <?php for ($i = 0; $i < 120; $i++): ?>
                <?php
                        $x = 6 + ($i * 6.5);
                        $h = 10 + (($i * 13) % 38);
                        $y = 40 - (int)($h / 2);
                        ?>
                <rect x="<?= $x ?>" y="<?= $y ?>" width="3" height="<?= $h ?>" rx="1" fill="url(#waveGradient)" />
                <?php endfor; ?>
            </svg>
        </div>

        <div class="player-controls">
            <button class="play-button" type="button" id="play-btn" aria-label="Play">
                <i class="fa-solid fa-play icon-lg" style="color: white" aria-hidden="true"></i>
            </button>
            <span class="time-display" id="current-time">0:00</span>
            <div class="progress-track" id="progress-track" role="slider" aria-label="Playback progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
                <div class="progress-bar">
                    <div class="progress-filled" id="progress-filled"></div>
                    <div class="progress-unfilled"></div>
                </div>
            </div>
            <span class="time-display" id="duration-time">0:00</span>
            <div class="volume-icon"><i class="fa-solid fa-volume-high icon-xl" aria-hidden="true"></i></div>
        </div>

        <audio id="detail-audio" preload="none" src="<?= e($audioUrl) ?>"></audio>
    </div>
    <div class="mb-3">
        <a class="toggle-btn" href="<?= e($audioDownloadUrl) ?>" download="<?= e($recId . '.mp3') ?>">Download <?= e($recId . '.mp3') ?></a>
    </div>
    <?php endif; ?>

    <div class="content-columns">
        <div class="column-left">
            <div class="metadata-section">
                <?php if ($informantName !== '' && $informantUrl !== ''): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Beulaiche | Informant</div>
                    <div class="metadata-value"><a href="<?= e($informantUrl) ?>"><?= e($informantName) ?></a></div>
                </div>
                <?php endif; ?>

                <?php if ($composerName !== '' && $composerUrl !== ''): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Bard | Composer</div>
                    <div class="metadata-value"><a href="<?= e($composerUrl) ?>"><?= e($composerName) ?></a></div>
                </div>
                <?php endif; ?>

                <?php if ($origin !== ''): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Aite tusail | Place of origin</div>
                    <div class="metadata-value"><?= e($origin) ?></div>
                </div>
                <?php endif; ?>

                <?php if ($genreName !== ''): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Seorsa | Genre</div>
                    <div class="metadata-value"><span class="tag <?= e($genreTagClass) ?>"><?= e($genreName) ?></span></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($rec['subgenres'])): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Fo-sheorsachan | Sub-genres</div>
                    <div class="metadata-value">
                        <?php foreach ($rec['subgenres'] as $sg): ?>
                        <a class="tag <?= e($genreTagClass) ?>" href="<?= e(base_path('/recordings?' . http_build_query(['subgenre' => [$sg]]))) ?>"><?= e($sg) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($rec['subjects'])): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Cuspairean | Subjects</div>
                    <div class="metadata-value">
                        <?php foreach ($rec['subjects'] as $s): ?>
                        <a class="tag tag-neutral" href="<?= e(base_path('/recordings?' . http_build_query(['subject' => [$s]]))) ?>"><?= e($s) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($rec['recording_date'])): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Ceann-latha claraidh | Date recorded</div>
                    <div class="metadata-value"><?= e((string)$rec['recording_date']) ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($rec['original_tape_no'])): ?>
                <div class="metadata-row">
                    <div class="metadata-label">Aireamh an teip | Tape No</div>
                    <div class="metadata-value"><span
                            class="tape-number"><?= e(trim((string)$rec['original_tape_no'] . ' ' . (string)($rec['original_tape_item_no'] ?? ''))) ?></span></div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($rec['first_line_chorus']) || !empty($rec['first_line_verse'])): ?>
            <div class="transcription-section">
                <div class="transcription-divider"></div>
                <h2 class="transcription-heading">First lines</h2>
                <div class="transcription-text">
                    <?php if (!empty($rec['first_line_chorus'])): ?><p><strong>Chorus:</strong> <?= e((string)$rec['first_line_chorus']) ?></p><?php endif; ?>
                    <?php if (!empty($rec['first_line_verse'])): ?><p><strong>Verse:</strong> <?= e((string)$rec['first_line_verse']) ?></p><?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($hasTranscription || $usePublicationNote): ?>
            <div class="transcription-section">
                <div class="transcription-divider"></div>
                <h2 class="transcription-heading">Tar-sgriobhadh | Transcription</h2>
                <button class="toggle-btn" type="button" id="toggle-transcription">Hide transcription</button>
                <div class="transcription-text" id="transcription-content">
                    <?php if (trim($transcriptionHtml) !== ''): ?>
                    <?= $transcriptionHtml ?>
                    <?php else: ?>
                    <p><?= nl2br(e($transcriptionText)) ?></p>
                    <?php endif; ?>
                </div>

                <?php if (!$usePublicationNote): ?>
                <a class="toggle-btn" href="<?= e(base_path('/recordings/' . rawurlencode($recId) . '/download-transcription')) ?>">Download transcription</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($hasNotes): ?>
            <div class="transcription-section">
                <div class="transcription-divider"></div>
                <h2 class="transcription-heading">Additional notes</h2>
                <div class="transcription-text">
                    <?php foreach ($notes as $label => $txt):
                            $txt = trim((string)$txt);
                            if ($txt === '') continue;
                            ?>
                    <p><strong><?= e($label) ?>:</strong><br><?= nl2br(e($txt)) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="column-right">
            <?php if ($informantName !== ''): ?>
            <div class="card informant-card">
                <div class="informant-photo">
                    <?php if ($informantImageUrl !== ''): ?>
                    <img src="<?= e($informantImageUrl) ?>" alt="<?= e($informantName) ?>" loading="lazy">
                    <?php endif; ?>
                </div>
                <div class="informant-name">
                    <?php if ($informantUrl !== ''): ?>
                    <a href="<?= e($informantUrl) ?>"><?= e($informantName) ?></a>
                    <?php else: ?>
                    <?= e($informantName) ?>
                    <?php endif; ?>
                </div>
                <?php if ($origin !== ''): ?><span class="informant-detail"><?= e($origin) ?></span><?php endif; ?>
                <?php if ($informantDetailLight !== ''): ?><span class="informant-detail-light"><?= e($informantDetailLight) ?></span><?php endif; ?>
                <?php if ($informantRecordingCount > 0): ?><span class="informant-count"><?= e((string)$informantRecordingCount) ?> recordings</span><?php endif; ?>
                <?php if ($informantUrl !== ''): ?>
                <a href="<?= e($informantUrl) ?>" class="informant-link">View profile <i class="fa-solid fa-arrow-right icon-sm" aria-hidden="true"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="related-section">
        <h2 class="related-heading">More related content</h2>
        <div class="related-row">
            <?php if (!empty($relatedRecords)): ?>
            <?php
                $cardClasses = ['mini-card-story', 'mini-card-custom', 'mini-card-song'];
                foreach ($relatedRecords as $idx => $related):
                    $className = $cardClasses[$idx % count($cardClasses)];
                    $relatedId = trim((string)($related['recording_id'] ?? ''));
                    if ($relatedId === '') continue;
                    $relatedTitle = trim((string)($related['title'] ?? ''));
                    if ($relatedTitle === '') {
                        $relatedTitle = $relatedId;
                    }
                    $relatedUrl = base_path('/recordings/' . rawurlencode($relatedId));
                    $metaParts = [];
                    $relatedDate = trim((string)($related['recording_date'] ?? ''));
                    $relatedGenre = trim((string)($related['genre_name'] ?? ''));
                    $relatedInformant = trim((string)(($related['informant_first'] ?? '') . ' ' . ($related['informant_last'] ?? '')));
                    if ($relatedDate !== '') $metaParts[] = $relatedDate;
                    if ($relatedGenre !== '') $metaParts[] = $relatedGenre;
                    if ($relatedInformant !== '') $metaParts[] = $relatedInformant;
                    $metaText = implode(' | ', $metaParts);
                ?>
            <a class="mini-card <?= e($className) ?>" href="<?= e($relatedUrl) ?>">
                <span class="mini-card-title"><?= e($relatedTitle) ?></span>
                <?php if ($metaText !== ''): ?>
                <span class="mini-card-meta"><?= e($metaText) ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <?php else: ?>
            <a class="mini-card mini-card-song" href="<?= e($backUrl) ?>">
                <span class="mini-card-title">Return to recordings list</span>
                <span class="mini-card-meta">No close matches found</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($hasAudio): ?>
<script>
(() => {
    const audio = document.getElementById('detail-audio');
    const playBtn = document.getElementById('play-btn');
    const currentTimeEl = document.getElementById('current-time');
    const durationEl = document.getElementById('duration-time');
    const progressTrack = document.getElementById('progress-track');
    const progressFilled = document.getElementById('progress-filled');

    if (!audio || !playBtn || !currentTimeEl || !durationEl || !progressTrack || !progressFilled) return;

    const formatTime = (seconds) => {
        if (!isFinite(seconds) || seconds < 0) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${String(s).padStart(2, '0')}`;
    };

    const setProgress = () => {
        const pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
        progressFilled.style.width = `${Math.max(0, Math.min(100, pct))}%`;
        progressTrack.setAttribute('aria-valuenow', String(Math.round(pct)));
        currentTimeEl.textContent = formatTime(audio.currentTime);
    };

    const setPlayIcon = (isPlaying) => {
        const icon = playBtn.querySelector('i');
        if (!icon) return;
        icon.className = isPlaying ? 'fa-solid fa-pause icon-lg' : 'fa-solid fa-play icon-lg';
        icon.style.color = 'white';
    };

    playBtn.addEventListener('click', () => {
        if (audio.paused) {
            audio.play();
        } else {
            audio.pause();
        }
    });

    progressTrack.addEventListener('click', (e) => {
        if (!audio.duration) return;
        const rect = progressTrack.getBoundingClientRect();
        const ratio = (e.clientX - rect.left) / rect.width;
        audio.currentTime = Math.max(0, Math.min(audio.duration, ratio * audio.duration));
    });

    progressTrack.addEventListener('keydown', (e) => {
        if (!audio.duration) return;
        const step = 5;
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            audio.currentTime = Math.min(audio.duration, audio.currentTime + step);
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            audio.currentTime = Math.max(0, audio.currentTime - step);
        }
    });

    audio.addEventListener('loadedmetadata', () => {
        durationEl.textContent = formatTime(audio.duration);
        setProgress();
    });

    audio.addEventListener('timeupdate', setProgress);
    audio.addEventListener('play', () => setPlayIcon(true));
    audio.addEventListener('pause', () => setPlayIcon(false));
    audio.addEventListener('ended', () => setPlayIcon(false));
})();
</script>
<?php endif; ?>

<?php if ($hasTranscription || $usePublicationNote): ?>
<script>
(() => {
    const toggle = document.getElementById('toggle-transcription');
    const content = document.getElementById('transcription-content');
    if (!toggle || !content) return;

    toggle.addEventListener('click', () => {
        const hidden = content.hasAttribute('hidden');
        if (hidden) {
            content.removeAttribute('hidden');
            toggle.textContent = 'Hide transcription';
        } else {
            content.setAttribute('hidden', 'hidden');
            toggle.textContent = 'Show transcription';
        }
    });
})();
</script>
<?php endif; ?>