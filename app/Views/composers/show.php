<?php
$title = trim(($composer['first_name'] ?? '') . ' ' . ($composer['last_name'] ?? '')) ?: $composer['composer_id'];
?>

<div class="mb-3">
    <a href="<?= e(base_path('/recordings')) ?>">&larr; Back to recordings</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h4 mb-1"><?= e($title) ?></h2>
        <div class="text-muted"><?= e($composer['composer_id']) ?></div>

        <?php if (!empty($composer['dates_raw'])): ?>
            <div class="mt-2"><strong>Dates:</strong> <?= e($composer['dates_raw']) ?></div>
        <?php endif; ?>

        <?php if (!empty($composer['place_of_birth'])): ?>
            <div class="mt-2"><strong>Place of birth:</strong> <?= e($composer['place_of_birth']) ?></div>
        <?php endif; ?>

        <?php if (!empty($composer['biography_html'])): ?>
        <?php
            $q = trim((string)e($_GET['q'] ?? ''));

            $biographyHtml = $q !== ''
            ? highlight_html_ga((string)$composer['biography_html'], $q)
            : (string)$composer['biography_html'];
        ?>
            <hr>
            <details class="record-transcription" open>
                <summary><strong>Biography</strong></summary>

                <?= $composer['biography_html'] ?>

            </details>
        <?php endif; ?>

    </div>
</div>



<h3 class="h5">Recordings</h3>
<div class="list-group">
    <?php foreach ($recs as $r): ?>
        <a class="list-group-item list-group-item-action" href="<?= e(base_path('/recordings/' . $r['recording_id'])) ?>">
            <div class="d-flex justify-content-between">
                <div><?= e($r['title'] ?: $r['recording_id']) ?></div>
                <div class="text-muted small">
                    <?= e(trim(($r['informant_first'] ?? '') . ' ' . ($r['informant_last'] ?? ''))) ?>
                    <?php if (!empty($r['recording_date'])): ?> · <?= e($r['recording_date']) ?><?php endif; ?>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>