<?php
$title = 'Informants';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'informants';
$headerTitle = 'Informants';

/*
$headerSearchOpen = header_filters_open($kw, $params, [
    'sort'     => 'name_asc',
    'per_page' => 20,
]);
*/
?>

<div class="container-fluid py-3">
    <div class="text-muted mb-2"><?= (int)$result['total'] ?> results</div>

    <form method="get" class="d-flex gap-2 align-items-end mb-3">
        <?php if (!empty($params['q'])): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($params['q']) ?>">
        <?php endif; ?>

        <div>
            <label for="sort" class="form-label">Sort</label>
            <select name="sort" id="sort" class="form-select">
                <option value="name_asc" <?= (($params['sort'] ?? 'name_asc') === 'name_asc') ? 'selected' : '' ?>>A–Z</option>
                <option value="name_desc" <?= (($params['sort'] ?? '') === 'name_desc') ? 'selected' : '' ?>>Z–A</option>
            </select>
        </div>

        <div>
            <label for="per_page" class="form-label">Per page</label>
            <select name="per_page" id="per_page" class="form-select">
                <?php foreach ([10, 20, 50, 100] as $n): ?>
                    <option value="<?= $n ?>" <?= ((int)($params['per_page'] ?? 20) === $n) ? 'selected' : '' ?>>
                        <?= $n ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <input type="hidden" name="page" value="1">

        <button type="submit" class="btn btn-primary">Apply</button>
    </form>

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