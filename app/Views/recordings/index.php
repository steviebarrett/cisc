<?php $title = 'Recordings'; ?>

<div class="row g-4">
    <div class="col-lg-3">
        <form method="get" class="card">
            <div class="card-header">Filters</div>
            <div class="card-body">

                <div class="mb-3">
                    <label class="form-label">Keyword</label>
                    <input class="form-control" name="q" value="<?= e($params['q']) ?>" placeholder="Title, first line, notes...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Genre</label>
                    <select class="form-select" name="genre">
                        <option value="">(Any)</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= e($g) ?>" <?= $params['genre'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Sub-genres</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                        <?php $selected = $params['subgenres']; ?>
                        <?php foreach ($subgenres_all as $sg): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subgenre[]" value="<?= e($sg) ?>"
                                    <?= in_array($sg, $selected, true) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= e($sg) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subjects</label>
                    <div class="border rounded p-2" style="max-height: 220px; overflow:auto;">
                        <?php $selected = $params['subjects']; ?>
                        <?php foreach ($subjects_all as $s): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="subject[]" value="<?= e($s) ?>"
                                    <?= in_array($s, $selected, true) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= e($s) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="has_en" value="1" <?= ($params['has_en'] === 1) ? 'checked' : '' ?>>
                    <label class="form-check-label">Includes English translation</label>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Per page</label>
                        <select class="form-select" name="per_page">
                            <?php foreach ([10,20,50,100] as $n): ?>
                                <option value="<?= $n ?>" <?= ((int)$params['per_page'] === $n) ? 'selected' : '' ?>><?= $n ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Sort</label>
                        <select class="form-select" name="sort">
                            <option value="date_asc"  <?= $params['sort']==='date_asc' ? 'selected' : '' ?>>Date ↑</option>
                            <option value="date_desc" <?= $params['sort']==='date_desc' ? 'selected' : '' ?>>Date ↓</option>
                            <option value="title_asc" <?= $params['sort']==='title_asc' ? 'selected' : '' ?>>Title A→Z</option>
                            <option value="title_desc"<?= $params['sort']==='title_desc' ? 'selected' : '' ?>>Title Z→A</option>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary">Apply</button>
                    <a class="btn btn-outline-secondary" href="<?= e(base_path('/recordings')) ?>">Reset</a>
                </div>

            </div>
        </form>
    </div>

    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted">
                <?= (int)$result['total'] ?> results
            </div>
        </div>

        <div class="list-group">
            <?php foreach ($result['rows'] as $row): ?>
                <a class="list-group-item list-group-item-action" href="<?= e(base_path('/recordings/' . $row['recording_id'])) ?>">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold"><?= e($row['title'] ?: $row['recording_id']) ?></div>
                            <div class="small text-muted">
                                <?= e(trim(($row['informant_first'] ?? '') . ' ' . ($row['informant_last'] ?? ''))) ?>
                                <?php if (!empty($row['recording_date'])): ?> · <?= e($row['recording_date']) ?><?php endif; ?>
                                <?php if (!empty($row['genre_name'])): ?> · <?= e($row['genre_name']) ?><?php endif; ?>
                                <?php if (!empty($row['includes_english_translation'])): ?> · EN<?php endif; ?>
                            </div>

                            <?php if (!empty($row['subgenres'])): ?>
                                <div class="mt-1">
                                    <?php foreach ($row['subgenres'] as $sg): ?>
                                        <span class="badge text-bg-secondary"><?= e($sg) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($row['subjects'])): ?>
                                <div class="mt-1">
                                    <?php foreach ($row['subjects'] as $s): ?>
                                        <span class="badge text-bg-light border"><?= e($s) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-end small text-muted">
                            <?= e($row['recording_id']) ?>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php require __DIR__ . '/../partials/pagination.php'; ?>
    </div>
</div>