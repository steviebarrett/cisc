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
        <!-- TODO this should probably be dynamic -->
        <p class="hero-stats">2,151 recordings from 161 voices across Cape Breton</p>
        <div class="hero-cta-row">
            <a href="<?= e(base_path('/map')) ?>" class="hero-cta-primary">Explore the Map</a>
            <a href="<?= e(base_path('/recordings')) ?>" class="hero-cta-secondary">Browse Recordings</a>
        </div>
    </div>
</section>

<section class="featured-informants">
    <h2 class="section-heading">Beulaichean | Featured Informants</h2>

    <!-- TODO informants need to be dynamic -->
    <!-- Note: I do not see a way to fetch featured informants -->
    <div class="informant-row">
        <div class="informant-card">
            <div class="informant-photo" style="background-image: url('src/photos/CURREB01.P1.png')"></div>
            <div class="informant-name"><a href="informant-detail.html">Effie Bella Currie</a></div>
            <div class="informant-community">MacAdam's Lake</div>
            <div class="informant-count">46 recordings</div>
        </div>

        <div class="informant-card">
            <div class="informant-photo" style="background-image: url('src/photos/MACLAL01.P1.jpg')"></div>
            <div class="informant-name"><a href="informant-detail.html">Lauchie MacLellan</a></div>
            <div class="informant-community">Dunvegan</div>
            <div class="informant-count">38 recordings</div>
        </div>

        <div class="informant-card">
            <div class="informant-photo" style="background-image: url('src/photos/MACNJD01.P1.jpg')"></div>
            <div class="informant-name"><a href="informant-detail.html">John Dan MacNeil</a></div>
            <div class="informant-community">Christmas Island</div>
            <div class="informant-count">29 recordings</div>
        </div>

        <div class="informant-card">
            <div class="informant-photo" style="background-image: url('src/photos/GILLM02.P1.jpg')"></div>
            <div class="informant-name"><a href="informant-detail.html">Murdoch Gillis</a></div>
            <div class="informant-community">Gillisdale</div>
            <div class="informant-count">24 recordings</div>
        </div>

        <div class="informant-card">
            <div class="informant-photo" style="background-image: url('src/photos/MACDA02.P1.jpg')"></div>
            <div class="informant-name"><a href="informant-detail.html">Angus MacDonald</a></div>
            <div class="informant-community">South West Margaree</div>
            <div class="informant-count">18 recordings</div>
        </div>

    </div>
</section>

<section class="collection-intro">
    <h2 class="collection-intro-heading">About the Collection</h2>
    <p class="collection-intro-text">Sruth nan Gàidheal (Gaelstream) is a digital archive of Scottish Gaelic oral traditions from Cape Breton, Nova Scotia. The collection preserves
        over 2,000 recordings of songs, stories, beliefs, proverbs, and customs from 161 tradition bearers across the island.</p>
    <p class="collection-intro-text">These recordings, gathered between the 1930s and 1990s, capture a living tradition carried from the Scottish Highlands and Islands to Nova
        Scotia. Each voice connects Cape Breton to communities in Uist, Barra, Mull, and the Scottish mainland - a bridge of language, music, and memory across the Atlantic.</p>
</section>