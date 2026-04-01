<?php
/**
 * Shared site header.
 *
 * Variables you can pass in from a page/controller:
 * - $activeNav: 'recordings' | 'informants' | 'places' | ''
 * - $headerTitle: string (optional)
 * - $enableSearchPanel: bool (optional)
 * - $searchPanelType: string (optional), e.g. 'recordings'
 * - $headerSearchOpen: bool (optional)
 */

$activeNav = $activeNav ?? '';
$headerTitle = $headerTitle ?? '';
$enableSearchPanel = !empty($enableSearchPanel);
$searchPanelType = $searchPanelType ?? '';
$headerSearchOpen = (bool)($headerSearchOpen ?? false);

function nav_link(string $href, string $label, string $key, string $activeNav): string {
    $active = ($key !== '' && $activeNav === $key) ? ' class="active"' : '';
    return '<a href="' . e($href) . '"' . $active . '>' . e($label) . '</a>';
}
?>

<?php
$isHome = ($activeNav === 'home');
$aboutGroupActive = in_array($activeNav, ['about', 'how_to_use', 'thanks'], true);
$navClasses = $isHome ? 'nav nav--transparent' : 'nav';
$logoPath = $isHome
    ? '/assets/images/logos/logo--dark-bg.svg'
    : '/assets/images/logos/logo--light-bg.svg';
?>

<nav class="<?= e($navClasses) ?>">
    <a href="<?= e(base_path('/')) ?>" class="nav-logo">
        <img src="<?= e(base_path($logoPath)) ?>" alt="Gaelstream">
    </a>

    <button class="nav-hamburger" type="button" onclick="document.body.classList.toggle('nav-open')" aria-label="Menu">
        <span></span><span></span><span></span>
    </button>

    <ul class="nav-links">
        <li><?= nav_link(base_path('/'), 'Home', 'home', $activeNav) ?></li>
        <li><?= nav_link(base_path('/recordings'), 'Recordings', 'recordings', $activeNav) ?></li>
        <li><?= nav_link(base_path('/informants'), 'Informants', 'informants', $activeNav) ?></li>
        <li><?= nav_link(base_path('/map'), 'Map', 'map', $activeNav) ?></li>
        <li class="nav-item-dropdown<?= $aboutGroupActive ? ' is-active' : '' ?>">
            <a class="nav-dropdown-toggle<?= $aboutGroupActive ? ' active' : '' ?>" aria-haspopup="true" aria-expanded="false">
                About
                <i data-lucide="chevron-down" class="icon-sm" aria-hidden="true"></i>
            </a>
            <ul class="nav-dropdown-menu" aria-label="About pages">
                <li><?= nav_link(base_path('/about'), 'About', 'about', $activeNav) ?></li>
                <li><?= nav_link(base_path('/how_to_use'), 'How To Use', 'how_to_use', $activeNav) ?></li>
                <li><?= nav_link(base_path('/thanks'), 'Thanks', 'thanks', $activeNav) ?></li>
            </ul>
        </li>
    </ul>

    <?php if ($enableSearchPanel): ?>
    <button class="nav-search-btn" type="button" data-bs-toggle="collapse" data-bs-target="#searchPanel" aria-expanded="<?= $headerSearchOpen ? 'true' : 'false' ?>"
        aria-controls="searchPanel">
        <i class="fa-solid fa-magnifying-glass icon-md" aria-hidden="true"></i>
        Search
    </button>
    <?php else: ?>
    <a class="nav-search-btn" href="<?= e(base_path('/recordings')) ?>">
        <i class="fa-solid fa-magnifying-glass icon-md" aria-hidden="true"></i>
        Search
    </a>
    <?php endif; ?>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dropdown = document.querySelector('.nav-item-dropdown');
    if (!dropdown) return;

    const toggle = dropdown.querySelector('.nav-dropdown-toggle');
    const closeDropdown = () => {
        dropdown.classList.remove('is-open');
        toggle?.setAttribute('aria-expanded', 'false');
    };

    toggle?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        const isOpen = dropdown.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', (event) => {
        if (!dropdown.contains(event.target)) {
            closeDropdown();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeDropdown();
        }
    });

    dropdown.querySelectorAll('.nav-dropdown-menu a').forEach((link) => {
        link.addEventListener('click', () => {
            closeDropdown();
        });
    });
});
</script>

<div class="nav-overlay">
    <ul class="nav-overlay-links">
        <li><?= nav_link(base_path('/'), 'Home', 'home', $activeNav) ?></li>
        <li><?= nav_link(base_path('/recordings'), 'Recordings', 'recordings', $activeNav) ?></li>
        <li><?= nav_link(base_path('/informants'), 'Informants', 'informants', $activeNav) ?></li>
        <li><?= nav_link(base_path('/map'), 'Map', 'map', $activeNav) ?></li>
        <li><?= nav_link(base_path('/about'), 'About', 'about', $activeNav) ?></li>
        <li><?= nav_link(base_path('/how_to_use'), 'How To Use', 'how_to_use', $activeNav) ?></li>
        <li><?= nav_link(base_path('/thanks'), 'Thanks', 'thanks', $activeNav) ?></li>
    </ul>

    <a class="nav-overlay-search" href="<?= e(base_path('/recordings')) ?>" onclick="document.body.classList.remove('nav-open')">
        <i class="fa-solid fa-magnifying-glass icon-xl" aria-hidden="true"></i>
        Search
    </a>
</div>

<?php if ($enableSearchPanel): ?>
<div class="collapse mt-2 <?= $headerSearchOpen ? 'show' : '' ?>" id="searchPanel">
    <?php if ($searchPanelType === 'recordings'): ?>
    <?php
        $searchPanel = $searchPanel ?? [];

        $params = $searchPanel['params'] ?? [];
        $places_all = $searchPanel['places_all'] ?? [];
        $genres = $searchPanel['genres'] ?? [];
        $subgenres_all = $searchPanel['subgenres_all'] ?? [];
        $subjects_all = $searchPanel['subjects_all'] ?? [];

        require __DIR__ . '/search/recording-search-panel.php'; ?>
    <?php endif; ?>
</div>
<?php endif; ?>