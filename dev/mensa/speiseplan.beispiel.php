<?php

require 'max_manager_gm_feed.php';

$feed = new MaxManagerGMFeed();

$weekdayMeals = $feed->getWeekdayMeals();

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Mensa Gummersbach / Speiseplan</title>
        <meta charset="utf-8" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>

        <style>
            *, body {
                line-height: 140%;
            }

            body {
            	font-size: 100%;
	            font-weight: 300;
                background-color: rgba(240, 240, 240, 1);
                font-family: 'Open Sans', sans-serif;
            }

                body > header,
                body > main,
                body > footer {
                    margin: 1rem auto;
                    padding: 1.8rem 1.8rem;
                    background-color: #fff;
                    max-width: 80%;
                }

                    body > header > h1 {
                        font-size: 2rem;
                        padding: 1.8rem 0rem;
                    }

                    body > main > section:last-child {
                        border-bottom: 0;
                        margin-bottom: 0;
                        padding-bottom: 0;
                    }

            h2.week_type {
                font-size: 1.8rem;
                margin-bottom: 1.8rem;
            }

            .week_overview {
                display: block;
            }

            .week_overview {
                padding-bottom: 2rem;
                margin-bottom: 2rem;
                border-bottom: 1px solid #dd1166;
            }

            .weekday_overview {
                display: block;
            }

                .weekday_overview .block {
                    display: inline-block;
                    vertical-align: top;
                    width: 180px;
                    height: 180px;
                    padding: 20px;
                    margin: 1rem 1rem 0.5rem 0;
                    border: 1px solid #fbfbfb;
                    position: relative;
                }

                .weekday_overview .block.day_indicator {
                    border: 1px solid #dd1166;
                }

                    .weekday_overview .block.day_indicator h3 {
                        font-size: 25px;
                        color: #000;
                        margin-bottom: 1rem;
                    }

                    .weekday_overview .block.day_indicator time {
                        position: absolute;
                        font-size: 1rem;
                    }

                .weekday_overview .block.meal {
                    background-color: #4952e1;
                    color: #fff;
                }

                .weekday_overview .block.meal.vegetarian {
                    background-color: #77cc00;
                    color: #333;
                }

                .weekday_overview .block.meal.side_dishes {
                    background-color: #9313c3;
                }

                    .weekday_overview .block.meal h4 {
                        margin-bottom: 1rem;
                        font-weight: 600;
                        font-size: 1rem;
                    }

                    .weekday_overview .block.meal p {
                        margin: 0.3rem 0;
                    }

                    .weekday_overview .block.meal p.price {
                        position: absolute;
                        bottom: 15px;
                        right: 20px;
                    }

            footer.legend {

            }

                footer.legend h2 {
                    font-size: 1.8rem;
                    margin-bottom: 1.8rem;
                }

                footer.legend .entry {
                    margin: 1rem 0rem;
                }

                    footer.legend .entry ul {
                        list-style-type: square;
                        color: #dd1166;
                        margin-left: 2rem;
                    }

                        footer.legend .entry ul span {
                            color: #000;
                        }

                    footer.legend .entry h3 {
                        font-size: 1.5rem;
                        margin: 1.8rem 0rem 1rem 0rem;
                    }
        </style>
    </head>
    <body>
        <header>
            <h1>Speiseplan der Mensa Gummersbach</h1>
        </header>

        <main>
            <?php
                $indices = array('thisweek', 'nextweek');
                $todayStr = (new DateTime())->format('Y-m-d');
            ?>
            <?php foreach($indices as $week): ?>
                <?php $currentWeekMeals = $weekdayMeals->$week; ?>

                <section class="week_overview">
                    <h2 class="week_type"><?= ($week === 'thisweek') ? "Diese Woche": "Nächste Woche" ?></h2>
                    <?php foreach($currentWeekMeals->weekdays as $weekday):  ?>
                        <?php
                            $date = DateTime::createFromFormat('Y-m-d', $weekday->date);
                            $dateStr = $date->format('d.m.Y');
                            $additionalClass = ($todayStr === $weekday->date) ? 'today': '';
                        ?>

                        <section class="weekday_overview <?= $additionalClass ?>">
                                <div class="block day_indicator">
                                    <h3><?= $weekday->weekdayName ?></h3>
                                    <time datetime="<?= $weekday->date ?>"><?= $dateStr ?></time>
                                </div>
                            <?php foreach($weekday->meals as $mealType => $mealData): ?>

                                <?php
                                    if(stripos($mealType, 'hinweis') !== false) {

                                        /* Hinweise sind eher der Legende zugehörig */
                                        if(!in_array($mealData->article, $weekdayMeals->legend->main))
                                            $weekdayMeals->legend->main[] = $mealData->article;

                                        continue;
                                    }

                                    $typeClass = '';

                                    if(stripos($mealType, 'beilag') !== false) {
                                        $typeClass = 'side_dishes';
                                    }
                                    else if(   stripos($mealType, 'vegeta') !== false
                                            || stripos($mealData->article, 'vegeta') !== false
                                            || stripos($mealData->desc, 'vegeta') !== false ) {
                                        $typeClass = 'vegetarian';
                                    }
                                ?>

                                <article class="block meal <?= $typeClass ?>">
                                    <header>
                                        <h4><?= $mealType ?></h4>
                                    </header>
                                    <p class="name"><?= $mealData->article ?></p>
                                    <p class="desc"><?= $mealData->desc ?></p>
                                    <p class="price"><?= ($mealData->price !== false) ? implode(' / ', $mealData->price): '' ?></p>
                                </article>

                            <?php endforeach; ?>
                        </section>

                    <?php endforeach; ?>
                </section>

            <?php endforeach; ?>
        </main>

        <footer class="legend">
            <h2>Zusatzstoffe, Allergene und Sonstiges</h2>
            <p class="price_info">Studierende / Bedienstete / Gäste*** (Preis in €)</p>
            <?php foreach($weekdayMeals->legend as $type => $entries): ?>

                <div class="entry <?= $type ?>">
                    <?php if($type === 'main'): ?>
                        <?php $entries = (array)$entries; ?>
                        <?php foreach($entries as  $entry): ?>
                            <p class="other_infos"><?= $entry ?></p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php
                            $headlineMap = array(
                                'additives'   => 'Zusatzstoffe',
                                'allergenics' => 'Allergene',
                                'others'      => 'Sonstiges'
                            );
                        ?>
                        <h3><?= $headlineMap[$type] ?></h3>
                        <ul>
                            <?php foreach($entries as $num => $desc): ?>
                                <li><span><?= $num . ' - ' . $desc; ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

            <?php endforeach; ?>
        </footer>
    </body>
</html>
