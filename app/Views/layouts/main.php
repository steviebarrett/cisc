<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Recordings') ?></title>
    <link rel="icon" type="image/png" href="<?= e(base_path('/assets/images/favicon.png')) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    <!-- Custom SCSS compiled CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.bootstrap5.min.css">

    <script src="https://kit.fontawesome.com/0b481d2098.js" crossorigin="anonymous"></script>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <!-- leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

</head>

<body class="<?= e($bodyClass ?? '') ?>">

    <?php require_once __DIR__ . '/../partials/header.php'; ?>




    <main class="<?= !empty($fullWidth) ? '' : 'container my-4 pt-3' ?>">
        <?= $content ?>
    </main>

    <footer class="footer">
        <div class="footer-links">
            <span>&copy;<?= date('Y') ?> Gaelstream</span>
            <a href="<?= e(base_path('/about')) ?>">Privacy Policy</a>
            <a href="<?= e(base_path('/how_to_use')) ?>">Terms of Use</a>
        </div>
        <div class="footer-logos">
            <img src="<?= e(base_path('/assets/images/logos/cape-breton-university--logo--mono.svg')) ?>" alt="Cape Breton University">
            <img src="<?= e(base_path('/assets/images/logos/dasg--logo--mono.svg')) ?>" alt="DASG">
        </div>
    </footer>

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

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof TomSelect === 'undefined') return;

        document.querySelectorAll('select.js-searchable-select').forEach((selectEl) => {
            if (selectEl.tomselect) return;

            const placeholder = selectEl.getAttribute('data-placeholder') || 'Select...';
            new TomSelect(selectEl, {
                create: false,
                maxOptions: 500,
                allowEmptyOption: true,
                placeholder,
                hidePlaceholder: false,
                plugins: {
                    dropdown_input: {}
                },
                render: {
                    no_results: () => '<div class="no-results">No matches found</div>'
                }
            });
        });
    });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
<script>
lucide.createIcons();
</script>

</html>
