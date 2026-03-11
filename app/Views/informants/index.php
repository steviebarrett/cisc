<?php
$title = 'Informants';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'informants';
$headerTitle = 'Informants';

$headerSearchOpen = header_filters_open($kw, $params, [
    'sort'     => 'name_asc',
    'per_page' => 20,
]);

ob_start();
?>
<form method="get">
    <div class="row g-3">
        <div class="col-12 col-lg-7">
            <label class="form-label">Keyword</label>
            <input class="form-control" name="q" value="<?= e($kw) ?>" placeholder="Name, community, notes...">
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
                <option value="name_asc"  <?= (($params['sort'] ?? 'name_asc') === 'name_asc') ? 'selected' : '' ?>>A→Z</option>
                <option value="name_desc" <?= (($params['sort'] ?? '') === 'name_desc') ? 'selected' : '' ?>>Z→A</option>
            </select>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
        <button class="btn btn-primary">Apply</button>
        <a class="btn btn-outline-secondary" href="<?= e(base_path('/informants')) ?>">Reset</a>
    </div>
</form>
<?php
$headerSearch = ob_get_clean();
?>

<div class="container-fluid py-3">
    <div class="text-muted mb-2"><?= (int)$result['total'] ?> results</div>

    <div class="list-group">
        <?php foreach ($result['rows'] as $row): ?>
            <?php
            $id = (string)$row['informant_id'];
            $name = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
            $ga = trim((string)($row['ainm'] ?? '') . ' ' . $row["cinneadh"]) ;
            $loc = trim(implode(' · ', array_filter([
                $row['community_origin_canada'] ?? '',
                $row['county'] ?? '',
                $row['tradition_scotland'] ?? '',
            ])));
            ?>
            <a class="list-group-item list-group-item-action" href="<?= e(base_path('/informants/' . rawurlencode($id))) ?>">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-semibold">
                            <?= $kw !== '' ? highlight_ga($name !== '' ? $name : $id, $kw) : e($name !== '' ? $name : $id) ?>
                            <?php if ($ga !== ''): ?>
                                <span class="text-muted fw-normal"> — <?= $kw !== '' ? highlight_ga($ga, $kw) : e($ga) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($loc !== ''): ?>
                            <div class="small text-muted"><?= $kw !== '' ? highlight_ga($loc, $kw) : e($loc) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end small text-muted">
                        <?= (int)($row['recording_count'] ?? 0) ?> recs<br>
                        <?= e($id) ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/../partials/pagination.php'; ?>
</div>