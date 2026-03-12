
<?php
/**
 * Shared site header.
 *
 * Variables you can pass in from a page/controller:
 * - $activeNav: 'recordings' | 'informants' | 'places' | ''
 * - $headerTitle: string (optional)
 * - $headerSearch: string (optional) HTML for the search/filter form body
 * - $headerSearchOpen: bool (optional) whether the search panel should start open
 */

$activeNav = $activeNav ?? '';
$headerTitle = $headerTitle ?? '';
$headerSearch = $headerSearch ?? '';
$headerSearchOpen = (bool)($headerSearchOpen ?? false);

function nav_link(string $href, string $label, string $key, string $activeNav): string {
    $active = ($key !== '' && $activeNav === $key) ? ' fw-semibold text-body' : '';
    return '<a class="text-decoration-none' . $active . '" href="' . e($href) . '">' . e($label) . '</a>';
}
?>

<header class="sticky-top bg-body border-bottom">
    <div class="container-fluid py-2">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-3">
                <a class="navbar-brand fw-semibold text-decoration-none" href="<?= e(base_path('/')) ?>">Cainnt ‘is Ceathramhan</a>

                <nav class="d-none d-md-flex gap-3">
                    <?= nav_link(base_path('/'), 'Home', 'home', $activeNav) ?>
                    <?= nav_link(base_path('/recordings'), 'Recordings', 'recordings', $activeNav) ?>
                    <?= nav_link(base_path('/informants'), 'Informants', 'informants', $activeNav) ?>
                    <!--?= nav_link(base_path('/places'), 'Map', 'places', $activeNav) ?-->
                    <?= nav_link(base_path('/map'), 'Map', 'map', $activeNav) ?>
                </nav>

                <?php if ($headerTitle !== ''): ?>
                    <span class="d-none d-lg-inline text-muted">/</span>
                    <span class="d-none d-lg-inline text-muted"><?= e($headerTitle) ?></span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($headerSearch !== ''): ?>
                    <button class="btn btn-outline-primary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#searchPanel"
                            aria-expanded="<?= $headerSearchOpen ? 'true' : 'false' ?>"
                            aria-controls="searchPanel">
                        Search / Filters
                    </button>
                <?php endif; ?>

                <button class="btn btn-outline-secondary d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#siteNav" aria-controls="siteNav">
                    Menu
                </button>
            </div>
        </div>

        <?php if ($headerSearch !== ''): ?>
            <div class="collapse mt-2 <?= $headerSearchOpen ? 'show' : '' ?>" id="searchPanel">
                <div class="card">
                    <div class="card-header">Search & Filters</div>
                    <div class="card-body">
                        <?= $headerSearch ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</header>

<div class="offcanvas offcanvas-end" tabindex="-1" id="siteNav" aria-labelledby="siteNavLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="siteNavLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-grid gap-2">
            <a class="btn btn-outline-secondary" href="<?= e(base_path('/recordings')) ?>">Recordings</a>
            <a class="btn btn-outline-secondary" href="<?= e(base_path('/informants')) ?>">Informants</a>
            <a class="btn btn-outline-secondary" href="<?= e(base_path('/places')) ?>">Places</a>
        </div>
    </div>
</div>
