<?php
$title = 'Thanks';
$headerTitle = 'Thanks';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'thanks';
$bodyClass = 'page-thanks';
$fullWidth = true;

?>

<section class="page-hero">
    <h1 class="page-hero-title">Taing | Thanks</h1>
    <p class="page-hero-subtitle">Acknowledging those who preserved these traditions</p>
</section>

<main class="content-container">
    <div class="opening-statement">
        <p class="opening-text">Gaelstream exists because of the generosity of tradition bearers who shared their songs, stories, and knowledge, and the dedication of collectors
            and institutions who preserved them. We honour their contributions and the communities that sustained these traditions across generations and oceans.</p>
    </div>

    <section class="content-section">
        <h2 class="content-section-heading">Luchd-glèidhidh an dualchais | The Tradition Bearers</h2>
        <p class="content-section-body">Our deepest gratitude goes to the 161 tradition bearers whose voices form this collection. They opened their homes and their memories to
            preserve a living heritage for future generations. Their names, stories, and recordings are the heart of Gaelstream.</p>
        <div class="quote-callout">
            <span class="quote-gaelic">'S ann anns na guthan a tha an dualchas beò.</span>
            <span class="quote-translation">It is in the voices that the heritage lives.</span>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Luchd-cruinneachaidh | Collectors &amp; Researchers</h2>
        <p class="content-section-body">The recordings in this collection were gathered by dedicated fieldworkers and researchers who travelled to communities across Cape Breton to
            document Gaelic traditions before they were lost.</p>
        <div class="name-chips">
            <span class="name-chip">John Shaw</span>
            <span class="name-chip">Hector Campbell</span>
            <span class="name-chip">Joe Neil MacNeil</span>
            <span class="name-chip">Fr. John Angus Rankin</span>
            <span class="name-chip">Kenneth Nilsen</span>
            <span class="name-chip">Margaret MacDonell</span>
            <span class="name-chip">Catriona Parsons</span>
            <span class="name-chip">Effie Rankin</span>
            <span class="name-chip">Jim Watson</span>
            <span class="name-chip">Sr. Margaret MacDonell</span>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Buidhnean | Institutions</h2>
        <div class="institution-list">
            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">Cape Breton University</span>
                    <span class="institution-role">Home of the Cape Breton Gaelic Folklore Collection</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">DASG - University of Glasgow</span>
                    <span class="institution-role">Digital archive hosting and technical infrastructure</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">Tobar an Dualchais</span>
                    <span class="institution-role">Scotland's premier oral tradition archive and collaborative partner</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">St. Francis Xavier University</span>
                    <span class="institution-role">Previous host of the digital collection</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">Nova Scotia Highland Village</span>
                    <span class="institution-role">Community partner and cultural preservation</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">Comhairle na Gàidhlig</span>
                    <span class="institution-role">Gaelic language and cultural advisory</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name">Cape Breton Centre for Heritage and Science</span>
                    <span class="institution-role">Regional heritage support</span>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Luchd-maoineachaidh | Funders</h2>
        <p class="content-section-body">This project has been made possible through the generous support of the following organizations.</p>
        <div class="funder-logos">
            <div class="funder-placeholder">Funder Logo 1</div>
            <div class="funder-placeholder">Funder Logo 2</div>
            <div class="funder-placeholder">Funder Logo 3</div>
            <div class="funder-placeholder">Funder Logo 4</div>
        </div>
    </section>

    <div class="closing-section">
        <p class="closing-text">Gaelstream is an ongoing project. If you have information about the tradition bearers or recordings in this collection, we welcome your
            contributions.</p>
        <a href="mailto:gaelstream@cbu.ca" class="closing-email">gaelstream@cbu.ca</a>
    </div>
</main>