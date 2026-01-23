<?php
$title = trim(($inf['first_name'] ?? '') . ' ' . ($inf['last_name'] ?? '')) ?: $inf['informant_id'];
?>

<div class="mb-3">
    <a href="<?= e(base_path('/recordings')) ?>">&larr; Back to recordings</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <h2 class="h4 mb-1"><?= e($title) ?></h2>
        <div class="text-muted"><?= e($inf['informant_id']) ?></div>

        <?php if (!empty($inf['ainm']) || !empty($inf['sloinneadh_breithe'])): ?>
            <div class="mt-2">
                <strong>Gaelic name:</strong>
                <?= e(trim(($inf['ainm'] ?? '') . ' ' . ($inf['sloinneadh_breithe'] ?? ''))) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($inf['dates_raw'])): ?>
            <div class="mt-2"><strong>Dates:</strong> <?= e($inf['dates_raw']) ?></div>
        <?php endif; ?>

        <?php if (!empty($inf['community_origin_canada']) || !empty($inf['county']) || !empty($inf['province_canada'])): ?>
            <div class="mt-2">
                <strong>Location:</strong>
                <?= e(trim(($inf['community_origin_canada'] ?? '') . ' ' . ($inf['county'] ?? '') . ' ' . ($inf['province_canada'] ?? ''))) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($inf['images'])): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($inf['images'] as $img): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <div class="small text-muted"><?= e($img['filename'] ?? '') ?></div>
                        <?php if (!empty($img['caption'])): ?>
                            <div><?= e($img['caption']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<h3 class="h5">Recordings</h3>
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