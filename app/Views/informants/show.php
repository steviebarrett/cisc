<?php
$activeNav = 'informants';
$headerTitle = 'Informants';
$bodyClass = 'page-informant-detail';
$fullWidth = true;

$nameGaelic = trim((string)(($inf['ainm'] ?? '') . ' ' . ($inf['cinneadh'] ?? '')));
$nameEnglish = trim((string)(($inf['first_name'] ?? '') . ' ' . ($inf['last_name'] ?? '')));
$nameDisplay = $nameEnglish !== '' ? $nameEnglish : trim((string)($inf['informant_id'] ?? ''));

$title = $nameDisplay;
if ($nameGaelic !== '') {
    $title = $nameGaelic . ' | ' . $title;
}

$backUrl = base_path('/informants');

$isValidImageFilename = static function (string $filename): bool {
    if ($filename === '' || strlen($filename) > 255) {
        return false;
    }

    if ($filename !== basename($filename)) {
        return false;
    }

    if (preg_match('/[\\\/\x00]/', $filename)) {
        return false;
    }

    return (bool)preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $filename);
};

$galleryImages = [];
if (!empty($inf['images']) && is_array($inf['images'])) {
    foreach ($inf['images'] as $img) {
        $filename = trim((string)($img['filename'] ?? ''));
        if (!$isValidImageFilename($filename)) {
            continue;
        }

        $galleryImages[] = [
            'url' => base_path('/media/informants/' . rawurlencode($filename)),
            'caption' => trim((string)($img['caption'] ?? '')),
        ];
    }
}

$imageCount = count($galleryImages);
$hasImageGallery = $imageCount > 1;
$primaryImageUrl = $imageCount > 0 ? (string)$galleryImages[0]['url'] : '';

$q = trim((string)($_GET['q'] ?? ''));
$biographyHtml = $q !== ''
    ? highlight_html_ga((string)($inf['biography_html'] ?? ''), $q)
    : (string)($inf['biography_html'] ?? '');
// Note - I remove <br>s and empty <p> tags so display can properly be handled by p margins and line height
$biographyHtml = preg_replace('~<br\s*/?>~i', '', $biographyHtml) ?? $biographyHtml;
$biographyHtml = preg_replace('~<p\b[^>]*>\s*(?:&nbsp;|\x{00A0}|\s)*</p>~iu', '', $biographyHtml) ?? $biographyHtml;

    $recordingCount = is_array($recs ?? null) ? count($recs) : 0;

    $genreClassMap = [
    'belief' => 'genre-belief',
    'biography' => 'genre-biography',
    'custom' => 'genre-custom',
    'expression' => 'genre-expression',
    'prayer' => 'genre-prayer',
    'rhyme' => 'genre-rhyme',
    'song' => 'genre-song',
    'story' => 'genre-story',
    'proverb' => 'genre-proverb',
    ];
    ?>

    <div class="page-container">
        <a href="<?= e($backUrl) ?>" class="back-link"><i data-lucide="arrow-left" class="icon-sm" aria-hidden="true"></i> Informants</a>

        <div class="profile-header">
            <div class="profile-photo-column">
                <?php if ($primaryImageUrl !== ''): ?>
                <div class="profile-photo-wrap">
                    <?php if ($hasImageGallery): ?>
                    <button type="button" class="profile-photo-button js-gallery-open" aria-label="Open photo gallery (<?= e((string)$imageCount) ?> photos)">
                        <img src="<?= e($primaryImageUrl) ?>" alt="<?= e($nameDisplay) ?>" class="profile-photo" loading="lazy">
                        <span class="photo-count-badge">
                            <i data-lucide="camera" class="icon-s" aria-hidden="true"></i>
                            <?= number_format($imageCount) ?> Photos
                        </span>
                    </button>
                    <?php else: ?>
                    <img src="<?= e($primaryImageUrl) ?>" alt="<?= e($nameDisplay) ?>" class="profile-photo" loading="lazy">
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="profile-photo" style="background: var(--color-placeholder);"></div>
                <?php endif; ?>
            </div>

            <div class="profile-info-column">
                <div class="name-block">
                    <?php if ($nameGaelic !== ''): ?>
                    <h1 class="name-gaelic"><?= e($nameGaelic) ?></h1>
                    <?php endif; ?>
                    <p class="name-english"><?= e($nameDisplay) ?></p>
                </div>

                <div class="metadata-pairs">
                    <?php if (!empty($inf['patronymic'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Sloinneadh | Patronymic</span>
                        <span class="metadata-value"><?= e(trim((string)$inf['patronymic'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inf['maiden_name'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Ainm-baistidh | Maiden name</span>
                        <span class="metadata-value"><?= e(trim((string)$inf['maiden_name'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inf['community_origin_canada'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Coimhearsnachd | Community</span>
                        <span class="metadata-value"><?= e(trim((string)$inf['community_origin_canada'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inf['county'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Siorrachd | County</span>
                        <span class="metadata-value"><?= e(trim((string)$inf['county'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inf['tradition_scotland'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Dualchas | Tradition (Scotland)</span>
                        <span class="metadata-value"><?= e(trim((string)$inf['tradition_scotland'])) ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($inf['dates_raw'])): ?>
                    <div class="metadata-pair">
                        <span class="metadata-label">Cinn-latha | Dates</span>
                        <span class="metadata-value"><?= e((string)$inf['dates_raw']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="recording-count-badge">
                    <span class="badge-pill"><?= number_format($recordingCount) ?> recordings</span>
                </div>
            </div>
        </div>

        <?php if (trim($biographyHtml) !== ''): ?>
        <div class="biography-section">
            <h2 class="section-heading">Eachdraidh-beatha | Biography</h2>
            <div class="biography-text">
                <?= $biographyHtml ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($recs)): ?>
        <div class="recordings-section">
            <h2 class="section-heading">Claraidhean | Recordings (<?= number_format($recordingCount) ?>)</h2>
            <div class="recording-list">
                <?php foreach ($recs as $r): ?>
                <?php
                    $recId = trim((string)($r['recording_id'] ?? ''));
                    $recTitle = trim((string)($r['title'] ?? ''));
                    if ($recTitle === '') {
                        $recTitle = $recId;
                    }
                    $genreName = trim((string)($r['genre_name'] ?? ''));
                    $genreLower = mb_strtolower($genreName);
                    $genreClass = '';
                    foreach ($genreClassMap as $needle => $className) {
                        if ($genreLower !== '' && str_contains($genreLower, $needle)) {
                            $genreClass = $className;
                            break;
                        }
                    }
                    $recUrl = base_path('/recordings/' . rawurlencode($recId));
                    $meta = trim((string)($r['recording_date'] ?? ''));
                    if ($genreName !== '') {
                        $meta .= ($meta !== '' ? ' · ' : '') . $genreName;
                    }
                    ?>
                <div class="recording-item <?= e($genreClass) ?>">
                    <div class="recording-content">
                        <div class="recording-info">
                            <a href="<?= e($recUrl) ?>" class="recording-title"><?= e($recTitle) ?></a>
                            <?php if ($meta !== ''): ?>
                            <span class="recording-meta"><?= e($meta) ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="recording-id"><?= e($recId) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($hasImageGallery): ?>
    <script id="informant-gallery-images" type="application/json">
    <?= json_encode($galleryImages, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
    </script>

    <div class="informant-lightbox" id="informant-lightbox" hidden>
        <button type="button" class="lightbox-backdrop js-gallery-close" aria-label="Close gallery"></button>
        <div class="lightbox-dialog" role="dialog" aria-modal="true" aria-label="Informant photo gallery">
            <div class="lightbox-header">
                <span class="lightbox-counter js-gallery-counter"></span>

                <button type="button" class="lightbox-close js-gallery-close" aria-label="Close gallery">
                    <i data-lucide="x" class="icon-xl" aria-hidden="true"></i>
                </button>
            </div>

            <button type="button" class="lightbox-nav lightbox-prev js-gallery-prev" aria-label="Previous photo">
                <i data-lucide="chevron-left" class="icon-xl" aria-hidden="true"></i>
            </button>

            <figure class="lightbox-figure">
                <img src="" alt="" class="lightbox-image js-gallery-image" loading="eager">
                <figcaption class="lightbox-meta">
                    <span class="lightbox-caption js-gallery-caption"></span>
                </figcaption>
            </figure>

            <button type="button" class="lightbox-nav lightbox-next js-gallery-next" aria-label="Next photo">
                <i data-lucide="chevron-right" class="icon-xl" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <script>
    (() => {
        const dataEl = document.getElementById('informant-gallery-images');
        const openBtn = document.querySelector('.js-gallery-open');
        const lightbox = document.getElementById('informant-lightbox');
        if (!dataEl || !openBtn || !lightbox) {
            return;
        }

        let images;
        try {
            images = JSON.parse(dataEl.textContent || '[]');
        } catch (err) {
            console.error('Could not parse informant gallery data', err);
            return;
        }

        if (!Array.isArray(images) || images.length < 2) {
            return;
        }

        const imageEl = lightbox.querySelector('.js-gallery-image');
        const counterEl = lightbox.querySelector('.js-gallery-counter');
        const captionEl = lightbox.querySelector('.js-gallery-caption');
        const prevBtn = lightbox.querySelector('.js-gallery-prev');
        const nextBtn = lightbox.querySelector('.js-gallery-next');
        const closeBtns = lightbox.querySelectorAll('.js-gallery-close');

        if (!imageEl || !counterEl || !captionEl || !prevBtn || !nextBtn || closeBtns.length === 0) {
            return;
        }

        let index = 0;

        const render = () => {
            const img = images[index] || {};
            imageEl.src = String(img.url || '');
            imageEl.alt = <?= json_encode($nameDisplay, JSON_UNESCAPED_UNICODE) ?> + ' photo ' + (index + 1);
            counterEl.innerHTML = 'Photos <span class="badge rounded-pill text-bg-light">' + (index + 1) + '/' + images.length + '</span>';

            const caption = String(img.caption || '').trim();
            captionEl.textContent = caption;
            captionEl.hidden = caption === '';
        };

        const open = (startIndex = 0) => {
            index = Number.isInteger(startIndex) ? startIndex : 0;
            if (index < 0 || index >= images.length) {
                index = 0;
            }

            render();
            lightbox.hidden = false;
            document.body.classList.add('lightbox-open');
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        };

        const close = () => {
            lightbox.hidden = true;
            document.body.classList.remove('lightbox-open');
        };

        const showNext = () => {
            index = (index + 1) % images.length;
            render();
        };

        const showPrev = () => {
            index = (index - 1 + images.length) % images.length;
            render();
        };

        openBtn.addEventListener('click', () => open(0));
        nextBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            showNext();
        });
        prevBtn.addEventListener('click', (event) => {
            event.stopPropagation();
            showPrev();
        });

        closeBtns.forEach((btn) => {
            btn.addEventListener('click', () => close());
        });

        document.addEventListener('keydown', (event) => {
            if (lightbox.hidden) {
                return;
            }

            if (event.key === 'Escape') {
                close();
                return;
            }

            if (event.key === 'ArrowRight') {
                showNext();
                return;
            }

            if (event.key === 'ArrowLeft') {
                showPrev();
            }
        });
    })();
    </script>
    <?php endif; ?>