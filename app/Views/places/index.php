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

$headerSearchOpen = header_filters_open($kw, $params, [
    'sort'     => 'name_asc',
    'per_page' => 20,
]);

ob_start();
?>
<!--form method="get">
    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <label class="form-label">Keyword</label>
            <input class="form-control" name="q" value="<?= e($kw) ?>" placeholder="Place name...">
        </div>

        <div class="col-6 col-lg-3">
            <label class="form-label">Per page</label>
            <select class="form-select" name="per_page">
                <?php foreach ([10,20,50,100] as $n): ?>
                    <option value="<?= $n ?>" <?= ((int)($params['per_page'] ?? 20) === $n) ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-lg-2">
            <label class="form-label">Sort</label>
            <select class="form-select" name="sort">
                <option value="name_asc"   <?= (($params['sort'] ?? 'name_asc') === 'name_asc') ? 'selected' : '' ?>>A→Z</option>
                <option value="name_desc"  <?= (($params['sort'] ?? '') === 'name_desc') ? 'selected' : '' ?>>Z→A</option>
                <option value="count_desc" <?= (($params['sort'] ?? '') === 'count_desc') ? 'selected' : '' ?>>Most</option>
                <option value="count_asc"  <?= (($params['sort'] ?? '') === 'count_asc') ? 'selected' : '' ?>>Least</option>
            </select>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_path('/places')) ?>">Reset</a>
    </div>
</form-->
<?php
$headerSearch = ob_get_clean();
?>


<div style="height:600px;" id="map"></div>
<script>
  var map = L.map('map').setView([46.2000, -60.7500], 7);

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
  }).addTo(map);


  let num_records = 3;
  var marker = L.marker([46.020763, -61.137168]).addTo(map);

  marker.bindPopup("<b>"+num_records+"</b> recordings").openPopup();

  var popup = L.popup();

  function onMapClick(e) {
    popup
      .setLatLng(e.latlng)
      .setContent("You clicked the map at " + e.latlng.toString())
      .openOn(map);
  }

  marker.on('click', onMapClick);

  /*
  var popup = L.popup()
    .setLatLng([46.020763, -61.137168])
    .setContent("<b>"+num_records+"</b> recordings")
    .openOn(map);
  */

</script>

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
