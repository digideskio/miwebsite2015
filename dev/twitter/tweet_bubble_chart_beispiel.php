<?php

require('twitter_home_timeline_cache.php');

if(file_exists('../../../../config/custom-config.php'))
  include_once('../../../../config/custom-config.php');



function getOrElse($val, $else) {
    if(is_array($val) && count($val) === 1)
      $val = $val[0];

    return $val ? $val: $else;
}


$envSettings = array(
    'screen_name'       => getOrElse( getenv('TWITTER_SCREEN_NAME'),             '<PLACEHOLDER>' ),
    'skip_user'         => getOrElse( explode(',', getenv('TWITTER_SKIP_USER')), '<PLACEHOLDER>' ),
    //'accepted_hashtags' => getOrElse( explode(',', getenv('TWITTER_ACCEPTED_HASHTAGS')), array('mikoeln', 'medieninformatik')), // usw.


    //'fetch_count'           => 20,
    //'keep_tweets_count'     => 50,
    //'cache_validity_period' => 10 * 60, // Sekunden
    //'autolink_attributes'   => array(), // assoziatives Array
    'stopwordlist_filepaths'  => array('stopwords/german.txt',
                                     'stopwords/english.txt'),

    /* Von TwitterAPIExchange benötigt */
    'oauth_access_token'        => getOrElse( getenv('TWITTER_OAUTH_ACCESS_TOKEN'),        '<PLACEHOLDER>' ),
    'oauth_access_token_secret' => getOrElse( getenv('TWITTER_OAUTH_ACCESS_TOKEN_SECRET'), '<PLACEHOLDER>' ),
    'consumer_key'              => getOrElse( getenv('TWITTER_CONSUMER_KEY'),              '<PLACEHOLDER>' ),
    'consumer_secret'           => getOrElse( getenv('TWITTER_CONSUMER_SECRET'),           '<PLACEHOLDER>' )
);

$settings =      isset($custom_config)
              && isset($custom_config['twitter_data'])
                ? $custom_config['twitter_data']
                : $envSettings;

$twitter = new TwitterHomeTimelineCache($settings);

?>
<!DOCTYPE html>
<html>
    <head>
        <title>Twitter Bubble Chart</title>
        <meta charset="utf-8" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
        <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css'>

        <style>

        *, body {
            line-height: 140%;
        }

        a {
            color: inherit;
            text-decoration: none;
            border-bottom: 1px solid;
        }

        a:hover {
            border-bottom: 1px solid #dd1166;
        }

        body {
          font-size: 100%;
          font-weight: 300;
            background-color: rgba(240, 240, 240, 1);
            font-family: 'Open Sans', sans-serif;
        }

            body > header,
            body > main {
                margin: 1rem auto;
                padding: 1.8rem 1.8rem;
                background-color: #fff;
                max-width: 80%;
            }

                body > header > h1 {
                    font-size: 2rem;
                    padding: 1.8rem 0rem;
                }

            .tweet {
                margin: 1.8rem 0;
                padding: 1rem;
                border-bottom: 1px solid #4952e1;
            }

                .tweet blockquote {

                }

                    .tweet blockquote p {
                        border-left: 0.4rem solid rgba(240, 240, 240, 1);
                        padding: 0.4rem;
                        padding-left: 1rem;
                    }

                    .tweet blockquote footer {
                        padding-left: 1.5rem;
                        margin: 1.2rem auto;
                    }

                    .tweet .twitter_actions {
                        transition: opacity 0.2s;
                        padding-left: 1.5rem;
                        opacity: 0;
                    }

                    .tweet:hover .twitter_actions {
                        transition: opacity 0.5s;
                        opacity: 1.0;
                    }

                        .tweet .twitter_actions a.btn {
                            text-decoration: none;
                            border: 1px solid #dd1166;
                            color: #dd1166;
                            display: inline-block;
                            padding: 5px;
                            width: 15px;
                            height: 15px;
                            line-height: 13px;
                            text-align: center;
                            margin: 0 0.2rem;
                        }

                        .tweet .twitter_actions a.btn:hover {
                            transition: background .2s, color .2s;
                            background: #dd1166;
                            color: #fff;
                        }

            .tweet:last-child {
                border: 0;
                margin-bottom: 0;
            }

            ul.error_list {
                list-style-type: square;
                color: #dd1166;
                margin-left: 2rem;
            }

                ul.error_list span {
                    color: #000;
                }

            ul.error_list + p {
                margin-top: 1.8rem;
            }


            .twitter_bubblechart_vis .bubblechart_svg text {
                font-size: 0.8rem;
                fill: #fff;
                font-weight: 400;
            }

        </style>
    </head>
    <body>
    <?php if( isset($tweets->errors) ): ?>
        <header>
            <h1>Fehler</h1>
        </header>
        <main>
            <ul class="error_list">
            <?php foreach( $tweets->errors as $error ): ?>
                <li><span>Errorcode <?= $error->code ?> - <?= $error->message ?></span></li>
            <?php endforeach; ?>

            </ul>
            <p>Wahrscheinlich wurden im Skript nicht die nötigen TOKENS, KEYS und SECRETS bzw. die Umgebungsvariablen gesetzt</p>
        </main>
    <?php else: ?>
        <header>
            <h1>Twitter Bubble Chart</h1>
        </header>
        <main>
            <section>
                <div class="twitter_bubblechart_vis"></div>
            </section>
        </main>

        <script src="//d3js.org/d3.v3.min.js" charset="utf-8"></script>
        <script src="lib/d3.layout.cloud.js"></script>
        <script>
            // Quelle: https://bl.ocks.org/mbostock/4063269

            var fontResizeFactor = 8,
                format = d3.format(',d'),
                aspectRatio = (window.screen.height || 9) / (window.screen.width || 16);

            var wordsRaw = <?= json_encode($twitter->getWordlist()); ?>,
                wordsFreq = [];

            for(var k in wordsRaw) {
                /* Sofern ein Wort weniger als drei Mal auftaucht,
                    wird es nicht berücksichtigt */
                if(wordsRaw[k] < 3)
                  continue;

                wordsFreq.push(
                    { text: k, cnt: wordsRaw[k] }
                );
            }

            var divNode = d3.select('.twitter_bubblechart_vis');
            var divNodeWidth = parseInt(divNode.style('width'));

            var size = {
                width: divNodeWidth,
                height: divNodeWidth * aspectRatio
            };
            

            /* FIXME: Experimentell */
            if(size.width < 500) {
                fontResizeFactor = 5;
                size.height *= 2;
            }

            var color = d3.scale.linear()
                    .domain([0, 1, 2, 3, 4, 5, 6, 10, 15, 20, 100])
                    .range(['#ddd', '#ccc', '#bbb', '#aaa', '#999', '#888', '#777', '#666', '#555', '#444', '#333', '#222']);

            var bubble = d3.layout.pack()
                .sort(null)
                .size([size.width, size.height])
                .value(function(d) { return d.cnt; })
                .padding(1.5);

            var svg = d3.select('.twitter_bubblechart_vis').append('svg')
                            .attr('class', 'bubblechart_svg')
                            .attr('width', size.width)
                            .attr('height', size.height)
                            .attr('preserveAspectRatio', 'xMidYMid meet')
                            .attr('viewBox', '0 0 ' + size.width + ' ' + size.height);

            function drawVis(words) {
                var node = svg.selectAll('.node')
                    .data(bubble.nodes( { children: wordsFreq } )
                            .filter(function(d) { return !d.children; }))
                    .enter().append('g')
                    .attr('class', 'node')
                    .attr('transform', function(d) { return 'translate(' + d.x + ',' + d.y + ')'; });

                node.append('title')
                    .text(function(d) { return d.text + ': ' + format(d.value); });

                node.append('circle')
                    .attr('r', function(d) { return d.r; })
                    .style('fill', function(d) { return color(d.value); });

                node.append('text')
                    .attr('dy', '.3em')
                    .style('text-anchor', 'middle')
                    .text(function(d) { var substr = d.text.substring(0, d.r / 5); return substr + ((substr.length < d.text.length) ? '...': ''); });
            }


            d3.select(window).on('resize', function() {
                var svgNode = d3.select('.bubblechart_svg');

                var divNode = d3.select('.twitter_bubblechart_vis');
                var divNodeWidth = parseInt(divNode.style('width'));

                svgNode.attr('width', divNodeWidth);
                svgNode.attr('height', divNodeWidth * aspectRatio);
            });

            drawVis();

        </script>
    <?php endif; ?>
    </body>
</html>
