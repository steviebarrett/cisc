<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Recordings') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>
</head>
<body>

<?php require_once __DIR__ . '/../partials/header.php'; ?>


<main class="container my-4 pt-3">
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