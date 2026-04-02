<?php
$title = 'Places';
$headerTitle = 'Places';

$browseType = $browse_type ?? 'canada';

$title = $browseType === 'scotland'
        ? 'Scottish Places'
        : 'Places';

$headerTitle = $title;

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'places';
?>

<div class="row">
  <div class="col-4">Location Details
    <div id="map-results"></div>
  </div>
  <div class="col-8" style="height:600px;" id="map"></div>
</div>

<script>window.BASE_PATH = <?= json_encode(base_path('')) ?>;</script>
<script id="informants-map-data" type="application/json">
  <?= json_encode($mapData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script type="module" src="<?= e(base_path('/assets/js/map-informants.js')) ?>"></script>

<div class="mb-3">
    <a href="<?= e(base_path('/places')) ?>" class="btn btn-sm btn-outline-secondary">Canadian Places</a>
    <a href="<?= e(base_path('/places/scotland')) ?>" class="btn btn-sm btn-outline-secondary">Scottish Places</a>
</div>

<?php if (($browse_type ?? '') === 'scotland'): ?>
    <p class="text-muted small">Places in Scotland</p>
<?php else: ?>
    <p class="text-muted small">Places in Canada</p>
<?php endif; ?>

<div class="container-fluid py-3">
    <div class="text-muted mb-2"><?= (int)$result['total'] ?> places</div>

    <div class="list-group">
        <?php foreach ($result['rows'] as $row): ?>
            <?php
            $place = (string)($row['place'] ?? '');
            $count = (int)($row['rec_count'] ?? 0);
            $recUrl = base_path('/recordings') . '?place=' . rawurlencode($place);
            ?>
            <a class="list-group-item list-group-item-action" href="<?= e($recUrl) ?>">
                <div class="d-flex justify-content-between">
                    <div class="fw-semibold">
                        <?= $kw !== '' ? highlight_ga($place, $kw) : e($place) ?>
                    </div>
                    <div class="text-muted small"><?= $count ?> recs</div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>
