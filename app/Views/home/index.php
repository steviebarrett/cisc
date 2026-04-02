<?php
$title = 'Sruth nan Gàidheal | Gaelstream';
$headerTitle = 'Home';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'home';
$bodyClass = 'page-homepage';

?>

<section class="hero">
    <div class="hero-mosaic-base"></div>
    <div class="hero-gradient-overlay"></div>
    <div class="hero-content">
        <img class="hero-logo-mark" src="<?= e(base_path('/assets/images/gaelstream-shape.svg')) ?>" alt="Gaelstream">
        <h1 class="hero-title">Cruinneachadh Beul-aithris Ghàidhlig Cheap Breatuinn &middot; Cape Breton Gaelic Folklore Project</h1>
        <img class="hero-map" src="<?= e(base_path('/assets/images/gaelstream-map.svg')) ?>" alt="Map of Nova Scotia and Cape Breton">
        <p class="hero-stats">2,151 recordings from 161 voices across Cape Breton</p>
        <div class="hero-cta-row">
            <a href="<?= e(base_path('/map')) ?>" class="hero-cta-primary">Explore the Map</a>
            <a href="<?= e(base_path('/recordings')) ?>" class="hero-cta-secondary">Browse Recordings</a>
        </div>
    </div>
</section>

<section class="featured-informants">
    <h2 class="section-heading">Beulaichean | Featured Informants</h2>

    <?php $featuredInformants = is_array($featuredInformants ?? null) ? $featuredInformants : []; ?>
    <div class="informant-row">
        <?php foreach ($featuredInformants as $fi): ?>
        <?php
                $informantId = trim((string)($fi['informant_id'] ?? ''));
                if ($informantId === '') {
                    continue;
                }

                $firstName = trim((string)($fi['first_name'] ?? ''));
                $lastName = trim((string)($fi['last_name'] ?? ''));
                $name = trim($firstName . ' ' . $lastName);

                if ($name === '') {
                    $gaelicName = trim((string)(($fi['ainm'] ?? '') . ' ' . ($fi['cinneadh'] ?? '')));
                    $name = $gaelicName !== '' ? $gaelicName : $informantId;
                }

                $community = trim((string)($fi['community_origin_canada'] ?? ''));
                if ($community === '') {
                    $community = trim((string)($fi['county'] ?? ''));
                }

                $imageFilename = trim((string)($fi['image_filename'] ?? ''));
                $photoStyle = '';
                if ($imageFilename !== '') {
                    $photoUrl = base_path('/media/informants/' . rawurlencode($imageFilename));
                    $photoStyle = 'background-image: url(\'' . e($photoUrl) . '\')';
                }

                $informantUrl = base_path('/informants/' . rawurlencode($informantId));
                $recordingCount = (int)($fi['recording_count'] ?? 0);
                ?>
        <div class="informant-card">
            <div class="informant-photo" <?= $photoStyle !== '' ? ' style="' . $photoStyle . '"' : '' ?>></div>
            <div class="informant-name"><a href="<?= e($informantUrl) ?>"><?= e($name) ?></a></div>
            <div class="informant-community"><?= e($community) ?></div>
            <div class="informant-count"><?= number_format($recordingCount) ?> recordings</div>
        </div>
        <?php endforeach; ?>

    </div>
</section>

<section class="collection-intro">
    <h2 class="collection-intro-heading">About the Collection</h2>
    <p class="collection-intro-text">Sruth nan Gàidheal (Gaelstream) is a digital archive of Scottish Gaelic oral traditions from Cape Breton, Nova Scotia. The collection preserves
        over 2,000 recordings of songs, stories, beliefs, proverbs, and customs from 161 tradition bearers across the island.</p>
    <p class="collection-intro-text">These recordings, gathered between the 1930s and 1990s, capture a living tradition carried from the Scottish Highlands and Islands to Nova
        Scotia. Each voice connects Cape Breton to communities in Uist, Barra, Mull, and the Scottish mainland - a bridge of language, music, and memory across the Atlantic.</p>
</section>