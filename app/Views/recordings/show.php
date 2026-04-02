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
$genreFilterUrl = $genreName !== ''
    ? base_path('/recordings?' . http_build_query(['genre' => $genreName]))
    : '';

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
// Note - I remove <br>s and empty <p> tags so display can properly be handled by p margins and line height
$transcriptionHtml = preg_replace('~<br\s*/?>~i', '', $transcriptionHtml) ?? $transcriptionHtml;
$transcriptionHtml = preg_replace('~<p\b[^>]*>\s*(?:&nbsp;|\x{00A0}|\s)*</p>~iu', '', $transcriptionHtml) ?? $transcriptionHtml;
    $transcriptionText = (string)($rec['transcription_text'] ?? '');
    $hasTranscription = (trim($transcriptionHtml) !== '') || (trim($transcriptionText) !== '');

    $usePublicationNote = false;
    if (!$hasTranscription && !empty($rec['notes3_publications'])) {
    $usePublicationNote = true;
    $transcriptionHtml = nl2br(e((string)$rec['notes3_publications']));
    }

    $notes = [
    'Notes (fieldnotes)' => $rec['notes1_additional_info'] ?? null,
    'Reference sources' => $rec['notes2_reference_sources'] ?? null,
    'Team notes' => $rec['notes4_team_notes'] ?? null,
    ];
    $hasNotes = array_filter($notes, fn($x) => trim((string)$x) !== '');
    $relatedRecords = is_array($relatedRecords ?? null) ? $relatedRecords : [];
    ?>

    <div class="page-container">
        <a href="<?= e($backUrl) ?>" class="back-link"><i data-lucide="arrow-left" class="icon-sm" aria-hidden="true"></i> Back to recordings</a>

        <div class="recording-header">
            <h1 class="recording-title"><?= e($recordingTitle) ?></h1>
            <?php if ($genreName !== ''): ?>
            <a class="tag <?= e($genreTagClass) ?>" href="<?= e($genreFilterUrl) ?>"><?= e($genreName) ?></a>
            <?php endif; ?>
        </div>

        <?php if ($hasAudio): ?>
        <div class="audio-player">
            <div class="waveform-area">
                <div id="waveform" class="waveform-host" aria-label="Audio waveform"></div>
            </div>

            <div class="player-controls">
                <button class="play-button" type="button" id="play-btn" aria-label="Play">
                    <i data-lucide="play" fill="#fff" class="icon-lg" style="color: white" aria-hidden="true"></i>
                </button>
                <span class="time-display" id="current-time">0:00</span>
                <div class="progress-track" id="progress-track" role="slider" aria-label="Playback progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" tabindex="0">
                    <div class="progress-bar">
                        <div class="progress-filled" id="progress-filled"></div>
                        <div class="progress-unfilled"></div>
                    </div>
                </div>
                <span class="time-display" id="duration-time">0:00</span>
                <button class="volume-icon" type="button" id="volume-btn" aria-label="Mute" aria-pressed="false">
                    <i data-lucide="volume-2" class="icon-xl" aria-hidden="true"></i>
                </button>
                <?php if(!empty($audioDownloadUrl)): ?>
                <a class="toggle-btn" href="<?= e($audioDownloadUrl) ?>" download="<?= e($recId . '.mp3') ?>">Download MP3</a>
                <?php endif; ?>
            </div>

            <audio id="detail-audio" preload="none" src="<?= e($audioUrl) ?>"></audio>
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
                        <div class="metadata-value"><a class="tag <?= e($genreTagClass) ?>" href="<?= e($genreFilterUrl) ?>"><?= e($genreName) ?></a></div>
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
                            <a class="tag tag-story" href="<?= e(base_path('/recordings?' . http_build_query(['subject' => [$s]]))) ?>"><?= e($s) ?></a>
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

                    <?php if ($recId !== ''): ?>
                    <div class="metadata-row">
                        <!-- TODO - need gaelic label -->
                        <div class="metadata-label">GF No</div>
                        <div class="metadata-value"><span class="tape-number"><?= e($recId) ?></span></div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($rec['first_line_chorus']) || !empty($rec['first_line_verse'])): ?>
                <div class="transcription-section first-lines">
                    <div class="transcription-divider"></div>
                    <h2 class="transcription-heading">A 'chiad sreathan | First lines</h2>
                    <div class="metadata-section">
                        <?php if (!empty($rec['first_line_chorus'])): ?>
                        <div class="metadata-row">
                            <div class="metadata-label">Sèist | Chorus</div>
                            <div class="metadata-value"><?= e((string)$rec['first_line_chorus']) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($rec['first_line_verse'])): ?>
                        <div class="metadata-row">
                            <div class="metadata-label">Rann | Verse</div>
                            <div class="metadata-value"><?= e((string)$rec['first_line_verse']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($hasTranscription || $usePublicationNote): ?>
                <div class="transcription-section">
                    <div class="transcription-divider"></div>
                    <h2 class="transcription-heading">Tar-sgriobhadh | Transcription</h2>
                    <div class="d-flex gap-3">
                        <button class="toggle-btn" type="button" id="toggle-transcription">Hide transcription</button>
                        <?php if (!$usePublicationNote): ?>
                        <a class="toggle-btn" href="<?= e(base_path('/recordings/' . rawurlencode($recId) . '/download-transcription')) ?>">Download transcription</a>
                        <?php endif; ?>
                    </div>
                    <div class="transcription-text" id="transcription-content">
                        <?php if (trim($transcriptionHtml) !== ''): ?>
                        <?= $transcriptionHtml ?>
                        <?php else: ?>
                        <p><?= nl2br(e($transcriptionText)) ?></p>
                        <?php endif; ?>
                    </div>


                </div>
                <?php endif; ?>

                <?php if ($hasNotes): ?>
                <div class="transcription-section notes-section">
                    <div class="transcription-divider"></div>
                    <div class="transcription-text">
                        <?php foreach ($notes as $label => $txt):
                            $txt = trim((string)$txt);
                            if ($txt === '') continue;
                            ?>
                        <p><strong><?= e($label) ?>:</strong><br><?= nl2br(e($txt)) ?></p>
                        <div class="transcription-divider equal"></div>
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
                    <a href="<?= e($informantUrl) ?>" class="informant-link">View profile <i data-lucide="arrow-right" class="icon-sm" aria-hidden="true"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="related-section">
            <h2 class="related-heading">Is dòcha gum bu toil leat na leanas | You might like the following</h2>
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
    <script src="https://unpkg.com/wavesurfer.js@7"></script>
    <script>
    (() => {
        const waveformEl = document.getElementById('waveform');
        const audio = document.getElementById('detail-audio');
        const playBtn = document.getElementById('play-btn');
        const currentTimeEl = document.getElementById('current-time');
        const durationEl = document.getElementById('duration-time');
        const progressTrack = document.getElementById('progress-track');
        const progressFilled = document.getElementById('progress-filled');
        const volumeBtn = document.getElementById('volume-btn');
        const player = document.querySelector('.audio-player');

        if (!waveformEl || !audio || !playBtn || !currentTimeEl || !durationEl || !progressTrack || !progressFilled || !volumeBtn || !player) return;

        const formatTime = (seconds) => {
            if (!isFinite(seconds) || seconds < 0) return '0:00';
            const m = Math.floor(seconds / 60);
            const s = Math.floor(seconds % 60);
            return `${m}:${String(s).padStart(2, '0')}`;
        };

        const setPlayIcon = (isPlaying) => {
            const iconName = isPlaying ? 'pause' : 'play';
            playBtn.innerHTML = `<i data-lucide="${iconName}" fill="#FFF" class="icon-lg" style="color: white" aria-hidden="true"></i>`;
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        };

        const setProgress = (currentTime, duration) => {
            const pct = duration > 0 ? (currentTime / duration) * 100 : 0;
            const safePct = Math.max(0, Math.min(100, pct));
            progressFilled.style.width = `${safePct}%`;
            progressTrack.setAttribute('aria-valuenow', String(Math.round(safePct)));
        };

        const setVolumeIcon = (isMuted) => {
            const iconName = isMuted ? 'volume-x' : 'volume-2';
            volumeBtn.innerHTML = `<i data-lucide="${iconName}" class="icon-xl" aria-hidden="true"></i>`;
            volumeBtn.setAttribute('aria-pressed', isMuted ? 'true' : 'false');
            volumeBtn.setAttribute('aria-label', isMuted ? 'Unmute' : 'Mute');
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        };

        const seekByRatio = (ratio, duration, seekFn) => {
            if (!duration || duration <= 0) return;
            const clampedRatio = Math.max(0, Math.min(1, ratio));
            seekFn(clampedRatio * duration);
        };

        const enableNativeFallback = () => {
            player.classList.add('audio-player--fallback');
            audio.controls = true;
            audio.preload = 'metadata';
            setVolumeIcon(Boolean(audio.muted || audio.volume === 0));

            audio.addEventListener('loadedmetadata', () => {
                durationEl.textContent = formatTime(audio.duration);
                setProgress(audio.currentTime, audio.duration);
            });

            audio.addEventListener('timeupdate', () => {
                currentTimeEl.textContent = formatTime(audio.currentTime);
                setProgress(audio.currentTime, audio.duration);
            });

            progressTrack.addEventListener('click', (event) => {
                if (!audio.duration) return;
                const rect = progressTrack.getBoundingClientRect();
                const ratio = (event.clientX - rect.left) / rect.width;
                seekByRatio(ratio, audio.duration, (time) => {
                    audio.currentTime = time;
                });
            });

            progressTrack.addEventListener('keydown', (event) => {
                if (!audio.duration) return;
                const step = 5;
                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    audio.currentTime = Math.min(audio.duration, audio.currentTime + step);
                }
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    audio.currentTime = Math.max(0, audio.currentTime - step);
                }
            });

            playBtn.addEventListener('click', () => {
                if (audio.paused) {
                    audio.play();
                } else {
                    audio.pause();
                }
            });

            volumeBtn.addEventListener('click', () => {
                audio.muted = !audio.muted;
                setVolumeIcon(audio.muted);
            });

            audio.addEventListener('volumechange', () => {
                setVolumeIcon(Boolean(audio.muted || audio.volume === 0));
            });

            audio.addEventListener('play', () => setPlayIcon(true));
            audio.addEventListener('pause', () => setPlayIcon(false));
            audio.addEventListener('ended', () => setPlayIcon(false));
        };

        if (!window.WaveSurfer) {
            enableNativeFallback();
            return;
        }

        let wavesurfer;
        try {
            wavesurfer = window.WaveSurfer.create({
                container: waveformEl,
                height: 80,
                url: audio.currentSrc || audio.src,
                waveColor: 'rgba(66, 62, 178, 0.45)',
                progressColor: '#1E99A3',
                cursorColor: '#423EB2',
                cursorWidth: 2,
                barWidth: 3,
                barGap: 2,
                barRadius: 2,
                normalize: true,
                dragToSeek: true,
                mediaControls: false,
                autoScroll: false,
            });
        } catch (error) {
            console.error('WaveSurfer failed to initialize', error);
            enableNativeFallback();
            return;
        }

        playBtn.addEventListener('click', () => {
            wavesurfer.playPause();
        });

        let lastVolume = 1;
        const getWaveVolume = () => {
            if (typeof wavesurfer.getVolume === 'function') {
                return wavesurfer.getVolume();
            }
            return 1;
        };

        const setWaveVolume = (value) => {
            if (typeof wavesurfer.setVolume === 'function') {
                wavesurfer.setVolume(value);
            }
        };

        volumeBtn.addEventListener('click', () => {
            const currentVolume = getWaveVolume();
            const isMuted = currentVolume <= 0.001;
            if (isMuted) {
                setWaveVolume(lastVolume > 0 ? lastVolume : 1);
                setVolumeIcon(false);
            } else {
                lastVolume = currentVolume;
                setWaveVolume(0);
                setVolumeIcon(true);
            }
        });

        wavesurfer.on('ready', () => {
            const duration = wavesurfer.getDuration();
            durationEl.textContent = formatTime(duration);
            currentTimeEl.textContent = '0:00';
            setProgress(0, duration);
            setVolumeIcon(getWaveVolume() <= 0.001);
        });

        wavesurfer.on('timeupdate', (time) => {
            const duration = wavesurfer.getDuration();
            currentTimeEl.textContent = formatTime(time);
            setProgress(time, duration);
        });

        progressTrack.addEventListener('click', (event) => {
            const duration = wavesurfer.getDuration();
            if (!duration) return;
            const rect = progressTrack.getBoundingClientRect();
            const ratio = (event.clientX - rect.left) / rect.width;
            seekByRatio(ratio, duration, (time) => {
                wavesurfer.setTime(time);
            });
        });

        progressTrack.addEventListener('keydown', (event) => {
            const duration = wavesurfer.getDuration();
            if (!duration) return;
            const step = 5;
            if (event.key === 'ArrowRight') {
                event.preventDefault();
                wavesurfer.setTime(Math.min(duration, wavesurfer.getCurrentTime() + step));
            }
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                wavesurfer.setTime(Math.max(0, wavesurfer.getCurrentTime() - step));
            }
        });

        wavesurfer.on('play', () => setPlayIcon(true));
        wavesurfer.on('pause', () => setPlayIcon(false));
        wavesurfer.on('finish', () => setPlayIcon(false));

        wavesurfer.on('error', (error) => {
            console.error('WaveSurfer playback error', error);
            if (!player.classList.contains('audio-player--fallback')) {
                enableNativeFallback();
            }
        });

        window.addEventListener('beforeunload', () => {
            wavesurfer.destroy();
        });
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