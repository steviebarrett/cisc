<?php
$title = 'About';
$headerTitle = 'About';
$activeNav = 'about';
$bodyClass = 'page-about';
$fullWidth = true;
?>

<section class="page-hero">
    <h1 class="page-hero-title">Mu dheidhinn | About</h1>
    <p class="page-hero-subtitle">The story behind the collection</p>
</section>

<main class="content-container">
    <section class="content-section">
        <h2 class="content-section-heading">An Cruinneachadh | The Collection</h2>
        <p class="content-section-body">Sruth nan Gaidheal (Gaelstream) is a digital archive preserving over 2,000 recordings of Scottish Gaelic oral traditions from Cape Breton,
            Nova Scotia. The collection spans songs, stories, beliefs, proverbs, and customs gathered from 161 tradition bearers across the island.</p>
        <p class="content-section-body">These recordings were collected between the 1930s and 1990s, capturing voices that carried centuries of Scottish Highland and Island
            tradition to the shores of Nova Scotia. Each recording represents not just a performance, but a living connection between Cape Breton communities and their ancestral
            homes in Uist, Barra, Mull, and across the Scottish mainland.</p>
        <p class="content-section-body">The archive is part of the Cape Breton Gaelic Folklore Collection, one of the largest repositories of Scottish Gaelic oral tradition outside
            Scotland. Through Gaelstream, these recordings are being made accessible to researchers, learners, and communities for the first time in a comprehensive digital format.
        </p>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Am Proiseact | The Project</h2>
        <p class="content-section-body">Gaelstream is a collaborative project between Cape Breton University and the Digital Archive of Scottish Gaelic (DASG) at the University of
            Glasgow. The project builds on earlier work by the Nova Scotia Gaelic Song Index (NSGSI), expanding the scope to include all genres of oral tradition and adding
            biographical profiles of the tradition bearers themselves.</p>
        <p class="content-section-body">The interactive map feature connects Cape Breton communities to their Scottish origins, making visible the geographic bridge that these
            traditions crossed. By linking recordings to informants, places, and traditions, Gaelstream reveals the web of connections that sustained Gaelic culture in Nova Scotia
            for over two centuries.</p>
    </section>

    <section class="content-section" style="gap: var(--space-lg);">
        <h2 class="content-section-heading">Com-pairtichean | Partners</h2>

        <div class="partners-list">
            <div class="partner-card card">
                <div class="partner-logo">
                    <img src="<?= e(base_path('/assets/images/logos/cape-breton-university--logo--mono.svg')) ?>" alt="Cape Breton University logo">
                </div>
                <div class="partner-info">
                    <span class="partner-name">Cape Breton University</span>
                    <span class="partner-description">Home of the Cape Breton Gaelic Folklore Collection and lead institution for the Gaelstream project.</span>
                </div>
            </div>

            <div class="partner-card card">
                <div class="partner-logo">
                    <img src="<?= e(base_path('/assets/images/logos/dasg--logo--mono.svg')) ?>" alt="DASG logo">
                </div>
                <div class="partner-info">
                    <span class="partner-name">DASG - University of Glasgow</span>
                    <span class="partner-description">The Digital Archive of Scottish Gaelic, providing the hosting platform and technical infrastructure for the archive.</span>
                </div>
            </div>

            <div class="partner-card card">
                <div class="partner-logo">NSGSI</div>
                <div class="partner-info">
                    <span class="partner-name">Nova Scotia Gaelic Song Index</span>
                    <span class="partner-description">The foundational project that catalogued Nova Scotia's Gaelic song tradition, forming the basis for Gaelstream's expanded
                        scope.</span>
                </div>
            </div>

            <div class="partner-card card">
                <div class="partner-logo">
                    <img src="<?= e(base_path('/assets/images/logos/tobar-an-dualchais.jpg')) ?>" alt="Tobar an Dualchais logo">
                </div>
                <div class="partner-info">
                    <span class="partner-name">Tobar an Dualchais</span>
                    <span class="partner-description">Scotland's premier oral tradition archive, providing the reference model and collaborative framework for Gaelstream.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Fios thugainn | Contact</h2>
        <p class="content-section-body">For inquiries about the collection, research access, or partnership opportunities, please contact the Gaelstream project team at Cape Breton
            University.</p>
        <a href="mailto:gaelstream@cbu.ca" class="contact-email">gaelstream@cbu.ca</a>
    </section>
</main>