<?php
$title = 'Sruth nan Gàidheal | Gaelstream';
$headerTitle = 'Home';

$kw = trim((string)($params['q'] ?? ''));

$activeNav = 'home';

?>
    <style>
        .featured-elements {
            display: flex;
            gap: 1rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .featured-elements li {
            flex: 1;
        }

        .featured_element_image {
            width: 150px;
            height: auto;
            border-radius: 5px;
        }

        .featured-elements figcaption {
            font-size: 0.9rem;
            margin-top: 0.5rem;
            color: #555;
        }

        a {
            color: #000;
            text-decoration: none;
        }
    </style>


    <div>
        <h1>Sruth nan Gàidheal</h1>
        <div>
            <p>
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam id lacus at lacus faucibus suscipit. In et gravida sem. Mauris pellentesque finibus est, a mollis eros euismod ut. Nam sit amet tincidunt urna, eu malesuada orci. Phasellus ac sem leo. Morbi imperdiet massa ac magna porttitor finibus. Sed non tincidunt nunc, eget ultrices nulla. Donec quis mi id augue euismod feugiat et eu ante. In ligula nunc, pretium vel arcu quis, mollis porttitor odio. Phasellus non odio rhoncus, lacinia elit non, hendrerit magna. Nam faucibus, turpis sit amet rutrum ultricies, velit leo rhoncus risus, eget molestie ligula purus at metus. In consectetur purus congue massa rhoncus placerat sed vel massa. Suspendisse non laoreet nibh. Morbi luctus neque vel vulputate bibendum. Pellentesque feugiat nisi vel mi ullamcorper, eu egestas elit volutpat. Morbi iaculis ut ex aliquet vehicula.
            </p>
        </div>
    </div>


    <!-- Featured Informants -->

    <div>
        <h3>Luchd-fiosrachaidh | Featured Informants</h3>

        <ul class="featured-elements">
            <?php foreach ($featuredInformants as $informant) : ?>
                <?php
                    $fullname = $informant['first_name'] . ' ' . $informant['last_name'];
                    $filename = $informant['filename'] ?? '';
                    $encoded  = rawurlencode($filename);
                    $imgUrl = base_path('/media/informants/' . $encoded);

                    // shouldn't be needed in production once all images are uploaded, but
                    // for now add a placeholder if the image file doesn't exist
                    //
                    $filePath = dirname(__DIR__, 2) . '/files/images/people/informants/' . $filename;
                    if (!file_exists($filePath)) {
                        $imgUrl = base_path('/media/informants/BEATDA02.P1.png');
                    }
                ?>

                <li>
                    <a href="/informants/<?= $informant['informant_id']  ?>" title="<?= $fullname ?>">
                        <figure>
                            <img class="featured_element_image" src="<?= $imgUrl ?>" alt="<?= $fullname ?>">
                            <figcaption>
                                <p><?= $fullname ?></p>
                                <p><?= $informant["place_name"] ?></p>
                                <p><?= $informant["num_recs"] ?> recordings</p>
                            </figcaption>
                        </figure>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>


        <h3>Gaelic Text Here | Featured Recordings</h3>
        <ul class="featured-elements">
            <?php foreach ($featuredRecordings as $row) :
                $title = $row["title"] ?? $row["recording_id"];
            ?>



                <li>
                    <a href="/recordings/<?= $row["recording_id"] ?>" title="<?= $title ?>">
                        <h4><?= $title ?></h4>
                        <p><?= e(trim((string)($row['informant_name'] ?? ''))) ?>
                        <?php if (!empty($row['genre_name'])): ?> · <?= e((string)$row['genre_name']) ?><?php endif; ?></p>

                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

