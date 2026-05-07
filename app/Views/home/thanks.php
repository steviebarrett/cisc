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
    <p class="page-hero-subtitle">Acknowledging the people, organizations, and communities who made this work possible</p>
</section>

<main class="content-container">
    <div class="opening-statement">
        <p class="opening-text">Gaelstream exists because of generosity: generosity of time, of memory, of voice, and of care. This project is rooted in communities across Cape Breton and beyond, and it reflects decades of knowledge-sharing, collaboration, and cultural stewardship. We acknowledge with deep gratitude all who contributed to building, sustaining, and sharing this work.</p>
    </div>

    <section class="content-section">
        <h2 class="content-section-heading">Luchd-glèidhidh an dualchais | The Tradition Bearers</h2>
        <p class="content-section-body">First and foremost, we honour the 161 tradition bearers whose songs, stories, language, and lived experiences form the heart of this collection. Their willingness to share—often in kitchens, living rooms, and community halls—has ensured that Gaelic cultural knowledge continues to live, move, and inspire new generations. We invite those exploring this site to meet the tradition bearers through the Contributors tab, where individual profiles and connections bring these voices and lives into fuller view.</p>
        <div class="quote-callout">
            <span class="quote-gaelic">'S ann anns na guthan a tha an dualchas beò.</span>
            <span class="quote-translation">It is in the voices that the heritage lives.</span>
        </div>

        <p class="content-section-body">We also extend heartfelt thanks to the many family members, friends, and community historians who shared memories, genealogical knowledge, photographs, and local context to inform the biographies newly written for each tradition bearer. They are listed in the drop-down box, below. Their care, accuracy, and generosity helped ensure that these lives and contributions are represented with respect, depth, and connection to place.</p>

        <div class="container my-0">
            <div class="accordion" id="namesAccordion">

                <!-- Group -->
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingNames">
                        <button class="accordion-button collapsed fw-semibold"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapseNames"
                                aria-expanded="false"
                                aria-controls="collapseNames">
                            Le taing do | With thanks to…
                        </button>
                    </h2>

                    <div id="collapseNames"
                         class="accordion-collapse collapse"
                         aria-labelledby="headingNames"
                         data-bs-parent="#namesAccordion">

                        <div class="accordion-body p-0">
                            <ul class="list-group list-group-flush">

                                <li class="list-group-item">Billy Aucoin, Massachusetts</li>
                                <li class="list-group-item">Clarence Barrett, Middle River Historical Society</li>
                                <li class="list-group-item">Helen Batherson, North Sydney</li>
                                <li class="list-group-item">Florence Beaton, Little Judique</li>
                                <li class="list-group-item">Gerard Beaton, Port Hood</li>
                                <li class="list-group-item">Gerard Beaton, Mabou</li>
                                <li class="list-group-item">Gwennie Beaton, Mabou</li>
                                <li class="list-group-item">Theresa & Johnny "Alec Rory" Beaton, Mabou Coal Mines</li>
                                <li class="list-group-item">Nancy Bonnell, Sydney</li>
                                <li class="list-group-item">Patsy Bourque, Troy</li>
                                <li class="list-group-item">Bev Brett, North River</li>
                                <li class="list-group-item">Callista Burridge, Deer Lake, Newfoundland</li>
                                <li class="list-group-item">Margaret "Peggy" Caimika, Sydney</li>
                                <li class="list-group-item">Alexander Campbell, Blackstone</li>
                                <li class="list-group-item">Angus L Campbell, Inverness</li>
                                <li class="list-group-item">Lloyd Campbell, Inverness</li>
                                <li class="list-group-item">Marion Campbell & Ellison Robertson, Ontario</li>
                                <li class="list-group-item">Eunice Carmichael, Tarbot</li>
                                <li class="list-group-item">The late Doris Carver, Port Hood</li>
                                <li class="list-group-item">Elizabeth Chandler, Whycocomagh</li>
                                <li class="list-group-item">The late Martin Chisholm, Melford</li>
                                <li class="list-group-item">Karen Cox, Sydney</li>
                                <li class="list-group-item">Ken Donovan, Old Sydney Society, Sydney</li>
                                <li class="list-group-item">Denise Yipp Doyle, Inverness</li>
                                <li class="list-group-item">An Drochaid Museum, Mabou</li>
                                <li class="list-group-item">Stephen Dugas, Halifax</li>
                                <li class="list-group-item">Rita Eastburg, California</li>
                                <li class="list-group-item">Deb Flanagan, Toronto</li>
                                <li class="list-group-item">Audrey Fraser, Aberdeen</li>
                                <li class="list-group-item">Alice Freeman, Inverness</li>
                                <li class="list-group-item">Barry George, Christmas Island</li>
                                <li class="list-group-item">Karen Gillies, Port Hood</li>
                                <li class="list-group-item">Aneas Gillis, Creignish</li>
                                <li class="list-group-item">Angela Gillis, Antigonish</li>
                                <li class="list-group-item">Angela Gillis, Halifax, Skye Glen</li>
                                <li class="list-group-item">Bernadine Gillis, Mabou Harbour</li>
                                <li class="list-group-item">Carol Gillis, Troy</li>
                                <li class="list-group-item">Gail Gillis, Halifax, Skye Glen</li>
                                <li class="list-group-item">Louise Gillis, Margaree</li>
                                <li class="list-group-item">Peter Gillis, Inverness</li>
                                <li class="list-group-item">Rose Gillis, Christmas Island</li>
                                <li class="list-group-item">Roy Gillis, Halifax</li>
                                <li class="list-group-item">Alison Gillis, Gillisdale</li>
                                <li class="list-group-item">Audrey Griffiths, Marion Bridge</li>
                                <li class="list-group-item">Dorothy Hanam, Truro</li>
                                <li class="list-group-item">Jackie Hanley, Little Judique</li>
                                <li class="list-group-item">Kaye Harrison, Nevada Valley</li>
                                <li class="list-group-item">Debbie Gaudes Hegge, Massachusetts</li>
                                <li class="list-group-item">Teena Hiltz, Sydney</li>
                                <li class="list-group-item">Jean Ingraham, Neil's Harbour</li>
                                <li class="list-group-item">Betty Lou Johnson, Red Islands</li>
                                <li class="list-group-item">Matt Jordan, Halifax</li>
                                <li class="list-group-item">Marianna Keeley, Glendale</li>
                                <li class="list-group-item">Catherine Kerr, Goose Cove</li>
                                <li class="list-group-item">Ann Marie Lacaase, British Columbia</li>
                                <li class="list-group-item">Cecilia Bryden Laing, Castle Bay</li>
                                <li class="list-group-item">Anna Mae Leadbetter, Baddeck</li>
                                <li class="list-group-item">Clifford Lee, Brook Village</li>
                                <li class="list-group-item">Ray MacArthur, Judique</li>
                                <li class="list-group-item">Dustin MacAulay, Grand River</li>
                                <li class="list-group-item">JC MacCormick, Framboise/Sydney</li>
                                <li class="list-group-item">Anne MacDermid, Westmount</li>
                                <li class="list-group-item">Donald Roddie MacDonald, Mabou Harbour</li>
                                <li class="list-group-item">Douglas MacDonald, Halifax</li>
                                <li class="list-group-item">Edna MacDonald, Glendale</li>
                                <li class="list-group-item">Hugh "Tupper" MacDonald, Marble Mountain</li>
                                <li class="list-group-item">Iain MacDonald, Creignish</li>
                                <li class="list-group-item">Jim MacDonald, Baddeck</li>
                                <li class="list-group-item">Joe L. MacDonald, Boisdale</li>
                                <li class="list-group-item">Kathleen MacDonald, Kingsville</li>
                                <li class="list-group-item">Larry MacDonald, Sydney</li>
                                <li class="list-group-item">Lawrence MacDonald, Brook Village</li>
                                <li class="list-group-item">Nona MacDonald-Dyke, North Shore</li>
                                <li class="list-group-item">Pauline MacDonald, Port Hawkesbury</li>
                                <li class="list-group-item">Theresa MacDonell, Judique</li>
                                <li class="list-group-item">Hughie "The Barber" MacEachen, Port Hawkesbury</li>
                                <li class="list-group-item">Janice MacEachern, Troy</li>
                                <li class="list-group-item">Ronnie MacEachen & Bernadette Campbell, Hawthorne</li>
                                <li class="list-group-item">Stanley MacEachen, Broad cove</li>
                                <li class="list-group-item">Alec MacEachern, Creignish</li>
                                <li class="list-group-item">Sister Catherine MacEachern, Sydney</li>
                                <li class="list-group-item">Heather MacEachern, Halifax</li>
                                <li class="list-group-item">Hughie MacEachern, Inverness</li>
                                <li class="list-group-item">Marian MacEachern, Creignish</li>
                                <li class="list-group-item">Mary "Joe" MacEachern, Broad Cove</li>
                                <li class="list-group-item">Gloria & Duncan MacFadyen, Aberdeen</li>
                                <li class="list-group-item">Kenena & Dougie MacFadyen, Aberdeen</li>
                                <li class="list-group-item">Geraldine MacFarlane, Inverness</li>
                                <li class="list-group-item">Susan MacFarlane, Southwest Margaree</li>
                                <li class="list-group-item">Francis MacGillivray, Glendale</li>
                                <li class="list-group-item">Mary MacGillivray, U.S.A.</li>
                                <li class="list-group-item">Donna & Donnie MacInnis, Glendale</li>
                                <li class="list-group-item">The late Frankie MacInnis, Creignish</li>
                                <li class="list-group-item">Hugh MacIntrye, Glendale</li>
                                <li class="list-group-item">Maureen MacIntyre, Glendale</li>
                                <li class="list-group-item">Norman MacIntyre, Boisdale</li>
                                <li class="list-group-item">Sandy MacKay, River Bourgeois</li>
                                <li class="list-group-item">Shirley MacKay, Grand River</li>
                                <li class="list-group-item">Don MacKeigan, Ottawa, Ontario</li>
                                <li class="list-group-item">Betty MacKenzie, Christmas Island</li>
                                <li class="list-group-item">David MacKenzie, Perth, Ontario</li>
                                <li class="list-group-item">Lynn MacKenzie, Christmas Island</li>
                                <li class="list-group-item">John Joe & Anne MacKenzie, Christmas Island</li>
                                <li class="list-group-item">Blaine MacKinnon, Boisdale</li>
                                <li class="list-group-item">Eileen MacKinnon, Deepdale</li>
                                <li class="list-group-item">Hughena MacKinnon, Christmas Island</li>
                                <li class="list-group-item">John MacKinnon, Scotchtown</li>
                                <li class="list-group-item">Stephen MacKinnon, Halifax</li>
                                <li class="list-group-item">Danny MacLean, Stewartdale</li>
                                <li class="list-group-item">The late Hugh Don MacLean, Scotsville</li>
                                <li class="list-group-item">Jamie MacLean, West Bay</li>
                                <li class="list-group-item">Jesselyn MacLean Ashworth, Scotsville</li>
                                <li class="list-group-item">The late Johnena MacLean, Blues Mills</li>
                                <li class="list-group-item">Michelle MacLean, Whycocomagh</li>
                                <li class="list-group-item">Pauline MacLean, Highland Village</li>
                                <li class="list-group-item">Sandy MacLean, West Bay</li>
                                <li class="list-group-item">Sam MacLean, Baddeck</li>
                                <li class="list-group-item">Wesley & Donna MacLean, Scotsville</li>
                                <li class="list-group-item">Leonard MacLellan, Valleyview, Alberta</li>
                                <li class="list-group-item">Patricia "Tish" MacLellan, Strathlorne</li>
                                <li class="list-group-item">Roddie MacLennan, Halifax, Deepdale</li>
                                <li class="list-group-item">Donald MacLeod Jr. Marion Bridge</li>
                                <li class="list-group-item">Erna MacLeod, Catalone</li>
                                <li class="list-group-item">Phyllis MacLeod, Gabarus</li>
                                <li class="list-group-item">Mary Joe MacMillan, Gussieville</li>
                                <li class="list-group-item">Bernie & Aileen MacNeil, Sydney</li>
                                <li class="list-group-item">"Boya" Micheal MacNeil, East Bay</li>
                                <li class="list-group-item">Cora & Roy MacNeil, Sydney</li>
                                <li class="list-group-item">Glen MacNeil, Sydney</li>
                                <li class="list-group-item">Glen MacNeil, Windsor, Ontario</li>
                                <li class="list-group-item">Jean "Dan Rory" MacNeil, Grasscove</li>
                                <li class="list-group-item">Marcie MacNeil, Sydney</li>
                                <li class="list-group-item">Marie MacNeil, Southwest Margaree</li>
                                <li class="list-group-item">Mary Lou & Hughie MacNeil, Benacadie</li>
                                <li class="list-group-item">Peggy MacNeil, Iona</li>
                                <li class="list-group-item">Theresa MacNeil, Antigonish</li>
                                <li class="list-group-item">Doreen MacLeod MacRae, Gabarus/Point Edward</li>
                                <li class="list-group-item">MA MacPherson, Creignish</li>
                                <li class="list-group-item">Hugh MacSween, Halifax</li>
                                <li class="list-group-item">Daniel McGean, Sydney</li>
                                <li class="list-group-item">Wendy McGean, Montreal</li>
                                <li class="list-group-item">Marie Moran, Port Hood</li>
                                <li class="list-group-item">Martha Morrsion, Baddeck</li>
                                <li class="list-group-item">Sandy Morrison, Sydney</li>
                                <li class="list-group-item">Sandy & Judy Morrsion, Grand River Falls</li>
                                <li class="list-group-item">Roddie & Judy Munro, Sydney</li>
                                <li class="list-group-item">Bradley Murphy, East Bay</li>
                                <li class="list-group-item">Joe & Cathy Murphy, Halifax</li>
                                <li class="list-group-item">Linda Murphy, Baddeck</li>
                                <li class="list-group-item">Monty Nicholson, Bucklaw</li>
                                <li class="list-group-item">Meaghan O'Handley, Grand Narrows</li>
                                <li class="list-group-item">Georgia Patton, Boston</li>
                                <li class="list-group-item">Linda Pearo, Kewstoke</li>
                                <li class="list-group-item">Tanya Peters, Middle River</li>
                                <li class="list-group-item">Catherine Phillips, Fort McMurray, Alberta</li>
                                <li class="list-group-item">Eugene Quigley, Truro</li>
                                <li class="list-group-item">Danny Rankin, Troy</li>
                                <li class="list-group-item">Leo Rankin, Williams Lake, British Columbia</li>
                                <li class="list-group-item">Dennis Shaw, North Sydney</li>
                                <li class="list-group-item">Arlene Smith, Mabou</li>
                                <li class="list-group-item">Walter Smith, St. Ann's Bay</li>
                                <li class="list-group-item">Colette Thomas, Sydney</li>
                                <li class="list-group-item">Bonny Thornhill's Book</li>
                                <li class="list-group-item">Nora Troke, Sydney</li>
                                <li class="list-group-item">Joanne Watts, Chestico Museum, Port Hood</li>
                                <li class="list-group-item">Rosemary Aboud Wheatley, Halifax</li>
                                <li class="list-group-item">Marie Wills, Little Judique</li>
                                <li class="list-group-item">Cathy Rankin Williams, Chester Basin</li>
                                <li class="list-group-item">Helen Williams, Connecticut</li>
                                <li class="list-group-item">Karen Wright, Halifax</li>

                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        <p class="content-section-body">We are especially grateful to Dr. John Shaw, Professor Emeritus at the University of Edinburgh, whose fieldwork and scholarship lie at the foundation of this collection. Through his dedicated collecting and careful documentation, these voices were preserved and made available for future generations, enabling the work presented here.</p>

    </section>

    <section class="content-section">
        <h2 class="content-section-heading">An Sgioba | The Team</h2>
        <p class="content-section-body">This work was sustained by an extraordinary project team whose skills, care, and shared commitment shaped every aspect of Gaelstream. We are deeply grateful for the generosity, patience, and intellectual labour each team member brought to the project. Most members of the team speak Gaelic to varying degrees, and we made a deliberate effort to use the language as much as possible in our meetings and day‑to‑day work, grounding the project in the living linguistic practices it seeks to support.</p>
        <p class="content-section-body">Team members contributed in many overlapping ways, including transcribing recordings; proof‑reading and correcting transcriptions; researching contributors and collection items; writing and revising biographies; expanding, correcting, and standardising metadata; managing and organizing digital files and project records; working directly with community members; promoting the project publicly; and writing about its aims and outcomes. As a team, we made a sustained effort to follow best practices in information management and documentation, supporting the long‑term integrity, usability, and care of the collection.</p>

        <ul>
            <li class="list-group-item">Heather Sparling (Project Lead)</li>
            <li class="list-group-item">Mary Jane Lamond (Project Coordinator)</li>
            <li class="list-group-item">Màiri Britton (Project Coordinator)</li>
            <li class="list-group-item">Stevie Barrett</li>
            <li class="list-group-item">Hannah Krebs</li>
            <li class="list-group-item">Chelsey MacPherson</li>
            <li class="list-group-item">Emily MacDonald</li>
            <li class="list-group-item">Brittany Rankin-MacDonald</li>
            <li class="list-group-item">Angus MacLeod</li>
            <li class="list-group-item">Dùghall MacPhee</li>
            <li class="list-group-item">Shannon MacMullin</li>
            <li class="list-group-item">Stacey MacLean</li>
            <li class="list-group-item">Carmen MacArthur</li>
            <li class="list-group-item">Effie Rankin</li>
        </ul>

        <h5>Student Researchers:</h5>
        <ul>
            <li class="list-group-item">Kaleb Deleskie</li>
            <li class="list-group-item">Erin MacKinnon</li>
            <li class="list-group-item">Vincent McDonald</li>
            <li class="list-group-item">Robert Pringle</li>
            <li class="list-group-item">Sarah Turnbull</li>
        </ul>
    </section>

    <section class="content-section">
        <h2 class="content-section-heading">Taic bho Bhuidhnean | Institutional Support</h2>
        <p class="content-section-body">We gratefully acknowledge the institutions whose sustained support—both financial and in‑kind—made this project possible. These organizations contributed far more than resources: they shared expertise, time, infrastructure, and deep cultural knowledge at every stage of the work. Collectively, they provided fluent Gaelic‑speaking staff and staff with extensive Gaelic cultural expertise; assisted with and advised on transcriptions; consulted thoughtfully on project development; and offered guidance shaped by long experience working with Gaelic collections and communities.</p>
        <p class="content-section-body">Our thanks extend in particular to colleagues at Tobar an Dualchais, where Chris Wright developed, at very short notice, the interactive map that illustrates connections between Cape Breton settlements and their original Scottish origins—the first map of its kind for this material.</p>
        <p class="content-section-body">At DASG (University of Glasgow), Stevie Barrett designed and implemented the database functionality that enables detailed, flexible, and nuanced searching across the collection.</p>
        <p class="content-section-body">We are also deeply grateful to Susan Cameron, now retired librarian at St. Francis Xavier University, whose early and enthusiastic support for this project included facilitating access to the original recordings held in the Cape Breton Gaelic Folklore Collection. Dr. Michael Linkletter, professor of Celtic Studies at StFX, also supported the project throughout its development.</p>
        <p class="content-section-body">The generosity, trust, and collaborative spirit of these institutions shaped not only what could be built, but also how it was built, and we acknowledge their contributions with sincere appreciation.</p>
        <div class="institution-list">


            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://sshrc-crsh.canada.ca/en.aspx" title="SSHRC" target="_blank">Social Sciences and Humanities Research Council of Canada</a> (SSHRC)</span>
                    <span class="institution-role">Funds postsecondary research, research training, and knowledge mobilization in the social sciences and humanities</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://www.cbu.ca/" title="Cape Breton University" target="_blank">Cape Breton University</a></span>
                    <span class="institution-role">A university committed to teaching, learning, and the future of Cape Breton Island, and home of the <a href="https://languageinlyrics.com/" title="Cainnt is Ceathramhan | Language and Lyrics project" target="_blank">Cainnt is Ceathramhan | Language and Lyrics project</a></span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://www.stfx.ca/" title="St. Francis Xavier University" target="_blank">St. Francis Xavier University</a></span>
                    <span class="institution-role">A top-ranked Atlantic Canadian university offering Celtic Studies and home of the Fr. Brewer Celtic Collection at the Angus L. Macdonald Library, where the original Cape Breton Gaelic Folklore Collection is maintained</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://dasg.ac.uk/" title="DASG" target="_blank">DASG</a>, <a href="https://gla.ac.uk" title="University of Glasgow" target="_blank">University of Glasgow</a></span>
                    <span class="institution-role">Online repository of digitised texts and lexical resources for Scottish Gaelic, providing archive hosting and technical infrastructure</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://www.novascotia.ca/government/gaelic-affairs" title="Iomairtean na Gàidhlig | Office of Gaelic Affairs" target="_blank">Iomairtean na Gàidhlig | Office of Gaelic Affairs</a></span>
                    <span class="institution-role">Works with partners to protect and promote the Gaelic language and culture</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://highlandvillage.novascotia.ca/" title="Baile nan Gàidheal | Highland Village Museum" target="_blank">Baile nan Gàidheal | Highland Village Museum</a></span>
                    <span class="institution-role">Community partner and folklife centre celebrating the story, language and living culture of Nova Scotia Gaels</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://gaeliccollege.edu/" title="Colaisde na Gàidhlig | The Gaelic College" target="_blank">Colaisde na Gàidhlig | The Gaelic College</a></span>
                    <span class="institution-role">Community partner promoting, preserving, and teaching Gaelic culture and its expressions</span>
                </div>
            </div>

            <div class="institution-item">
                <div class="institution-icon">
                    <i data-lucide="landmark" class="icon-lg"></i>
                </div>
                <div class="institution-info">
                    <span class="institution-name"><a href="https://www.tobarandualchais.co.uk/" title="Tobar an Dualchais" target="_blank">Tobar an Dualchais</a></span>
                    <span class="institution-role">Dedicated to the presentation and promotion of audio recordings of Scotland’s cultural heritage and to create a broader and deeper understanding of their content</span>
                </div>
            </div>

        </div>
    </section>

    <section class="content-section">
        <!--h2 class="content-section-heading">Luchd-maoineachaidh | Funders</h2>
        <p class="content-section-body">This project has been made possible through the generous support of the following organizations.</p-->
        <div class="funder-logos">
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/sshrc.png')) ?>" alt="SSHRC"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/cbu.png')) ?>" alt="Cape Breton University"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/stfx.png')) ?>" alt="St. Francis Xavier University"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/dasg.png')) ?>" alt="DASG"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/gaelic_affairs.png')) ?>" alt="Gaelic Affairs"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/highland_village.png')) ?>" alt="Highland Village"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/gaelic_college.png')) ?>" alt="Gaelic College"></div>
            <div class="funder-placeholder"><img src="<?= e(base_path('/assets/images/logos/thanks/tobar.png')) ?>" alt="Tobar an Dualchais"></div>
        </div>
    </section>

    <div class="closing-section">
        <p class="closing-text">Gaelstream is an ongoing project. If you have information about the tradition bearers or recordings in this collection, we welcome your
            contributions.</p>
        <a href="mailto:heather_sparling@cbu.ca" class="closing-email">heather_sparling@cbu.ca</a>
    </div>
</main>