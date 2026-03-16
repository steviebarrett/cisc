<?php

// pull together the informant's name
$title = !empty($inf["ainm"]) ? e($inf['ainm'] . ' ' . $inf['cinneadh']) . ' ' : '';
$title .= '| ' . trim(e($inf['first_name']));
$title .= !empty($inf["nickname"]) ? ' (' . $inf["nickname"] . ')': '';
$title .=  ' ' . $inf['last_name'];

?>

<div class="mb-3">
    <a href="<?= e(base_path('/informants')) ?>">&larr; informants</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h4 mb-1"><?= $title ?></h2>
        <!--div class="text-muted"><?= e($inf['informant_id']) ?></div-->

        <?php if (!empty($inf['patronymic'])): ?>
            <div class="mt-2">
                <strong>Sloinneadh | Patronymic:</strong>
                <?= e(trim(($inf['patronymic'] ?? ''))); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['maiden_name'])): ?>
            <div class="mt-2">
                <strong>Sloinneadh-breithe | Maiden Name:</strong>
                <?= e(trim(($inf['maiden_name'] ?? ''))); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['community_origin_canada'])): ?>
            <div class="mt-2">
                <strong>Coimhearsnachd Thùsail | Community of Origin:</strong>
                <?= e(trim(($inf['community_origin_canada']))) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['county'])): ?>
            <div class="mt-2">
                <strong>Siorramachd | County:</strong>
                <?= e(trim(($inf['county']))) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['tradition_scotland'])): ?>
            <div class="mt-2">
                <strong>Dualchas (Alba) | Tradition (Scotland):</strong>
                <?= e(trim(($inf['tradition_scotland']))) ?? ''; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['dates_raw'])): ?>
            <div class="mt-2"><strong>Cinn-latha | Dates:</strong> <?= e($inf['dates_raw']) ?></div>
        <?php endif; ?>

    </div>
</div>

<?php if (!empty($inf['images'])): ?>
    <div class="mt-3">
        <div class="row g-2">
            <?php foreach ($inf['images'] as $img):
                $caption = $img["caption"] ?? "";
                ?>
                <?php
                    $imgUrl = base_path('/media/informants/' . rawurlencode($img["filename"]));
                ?>
                <figure class="col-6 col-md-4 col-lg-3">
                    <a href="<?= e($imgUrl) ?>" target="_blank" rel="noopener">
                        <img
                                src="<?= e($imgUrl) ?>"
                                class="img-fluid rounded border"
                                loading="lazy"
                                alt="<?= e($caption) ?>"
                        >
                    </a>
                    <figcaption><?= e($caption); ?></figcaption>
                </figure>

            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($inf['biography_html'])): ?>
<div class="card mb-3">
    <div class="card-body">
        <?php
        $q = trim((string)e($_GET['q'] ?? ''));

        $biographyHtml = $q !== ''
                ? highlight_html_ga((string)$inf['biography_html'], $q)
                : (string)$inf['biography_html'];
        ?>
        <details class="record-transcription" open>
            <summary><strong>Biography</strong></summary>

            <?= $inf['biography_html'] ?>
        </details>
    </div>
</div>
<?php endif; ?>


<?php if (!empty($recs)): ?>
<h3 class="h5" id="informant-recordings">Recordings</h3>
<div class="list-group">
    <?php foreach ($recs as $r): ?>
        <a class="list-group-item list-group-item-action" href="<?= e(base_path('/recordings/' . $r['recording_id'])) ?>">
            <div class="d-flex justify-content-between">
                <div><?= e($r['title'] ?: $r['recording_id']) ?></div>
                <div class="text-muted small">
                    <?= e($r['recording_date'] ?? '') ?><?= !empty($r['genre_name']) ? ' · ' . e($r['genre_name']) : '' ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>