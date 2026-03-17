<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Recordings') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>

  <script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>

  <!-- leaflet JS -->
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""></script>

</head>
<body>

<?php require_once __DIR__ . '/../partials/header.php'; ?>




<main class="<?= !empty($fullWidth) ? '' : 'container my-4 pt-3' ?>">
    <?= $content ?>
</main>

<script>
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !e.metaKey && !e.ctrlKey && !e.altKey) {
            const tag = (document.activeElement?.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;
            e.preventDefault();
            const el = document.getElementById('searchPanel');
            if (!el) return; // page may not provide a search panel
            bootstrap.Collapse.getOrCreateInstance(el).show();
            setTimeout(() => el.querySelector('input[name="q"]')?.focus(), 50);
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
