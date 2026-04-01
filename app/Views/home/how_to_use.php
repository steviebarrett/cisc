<?php
$title = 'How To Use This Site';
$headerTitle = 'How To Use';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'how_to_use';
$bodyClass = 'page-how-to-use';
$fullWidth = true;

?>

<section class="page-hero">
    <h1 class="page-hero-title">Mar a chleachdas tu | How To Use</h1>
    <p class="page-hero-subtitle">A guide to exploring the archive</p>
</section>

<main class="content-container">
    <section class="content-section">
        <h2 class="content-section-heading">A' rannsachadh | Searching</h2>
        <p class="content-section-intro">Gaelstream offers several ways to find recordings in the collection.</p>

        <div class="step-cards">
            <div class="step-card">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Browse by title</div>
                    <p class="step-description">Visit the Recordings page to see all 2,151 items. Sort by newest, oldest, or alphabetically. Use the per-page control to see more
                        results at once.</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Search by keyword</div>
                    <p class="step-description">Use the keyword search to find recordings by title. For example, search 'Ailein Duinn' to find recordings of this well-known song.
                    </p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Search transcriptions</div>
                    <p class="step-description">Many recordings have Gaelic transcriptions. Use the transcription search to find specific words or phrases within the recorded text
                        - a powerful tool for linguistic research.</p>
                </div>
            </div>

            <div class="step-card">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Filter by genre</div>
                    <p class="step-description">Narrow results by genre: Song, Story, Belief, Custom, Biography, or Proverb. Combine with other filters for precise results.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">A' tuigsinn an dàta | Understanding Metadata</h2>
        <p class="content-section-intro">Each recording page displays detailed metadata in both Gaelic and English.</p>

        <div class="metadata-list">
            <div class="metadata-item">
                <span class="metadata-label">Beulaiche | Informant</span>
                <span class="metadata-description">The tradition bearer who performed the recording. Click their name to see their full profile and other recordings.</span>
            </div>

            <div class="metadata-item">
                <span class="metadata-label">Àite tùsail | Place of origin</span>
                <span class="metadata-description">The Cape Breton community associated with the informant.</span>
            </div>

            <div class="metadata-item">
                <span class="metadata-label">Seòrsa | Genre</span>
                <span class="metadata-description">The type of oral tradition: Song (Òran), Story (Sgeulachd), Belief (Creideamh), Custom (Cleachdadh), Biography
                    (Eachdraidh-beatha), or Proverb (Seanfhacal).</span>
            </div>

            <div class="metadata-item">
                <span class="metadata-label">Fo-sheòrsachan | Sub-genres</span>
                <span class="metadata-description">More specific categories within the genre, such as waulking song, lullaby, or hero tale.</span>
            </div>

            <div class="metadata-item">
                <span class="metadata-label">Cuspairean | Subjects</span>
                <span class="metadata-description">Topics covered in the recording, such as emigration, fishing, or supernatural.</span>
            </div>

            <div class="metadata-item">
                <span class="metadata-label">Àireamh an teip | Tape No</span>
                <span class="metadata-description">The original tape reference number from the Cape Breton Gaelic Folklore Collection.</span>
            </div>
        </div>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">A' cleachdadh a' mhapa | Using the Map</h2>
        <p class="content-section-body">The interactive map shows where tradition bearers lived across eastern Nova Scotia, with a secondary view showing their ancestral
            connections to communities in Scotland.</p>
        <p class="content-section-body">Use the three tabs - Places, People, and Traditions - to explore different dimensions of the collection geographically. Click any community
            dot to see the informants from that area and access their recordings directly.</p>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Comhairlean | Tips</h2>

        <div class="tips-list">
            <div class="tip-item">
                <div class="tip-bullet"></div>
                <p class="tip-text">Use the 'Has transcription' filter to find recordings with searchable Gaelic text.</p>
            </div>

            <div class="tip-item">
                <div class="tip-bullet"></div>
                <p class="tip-text">Click an informant's name on any recording page to discover all their other recordings.</p>
            </div>

            <div class="tip-item">
                <div class="tip-bullet"></div>
                <p class="tip-text">The map's Traditions tab reveals which communities share heritage from the same region in Scotland.</p>
            </div>

            <div class="tip-item">
                <div class="tip-bullet"></div>
                <p class="tip-text">Toggle between Gaelic (GD) and English (EN) labels on the map to see place names in both languages.</p>
            </div>

            <div class="tip-item">
                <div class="tip-bullet"></div>
                <p class="tip-text">Many informants have biographical essays - read these for rich context about the tradition bearer's life and community.</p>
            </div>
        </div>
    </section>
</main>