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

$tweets = $twitter->getTweets( /* Standardwert = 10 */ );


?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
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
            <h1>Tweets</h1>
        </header>
        <main>
            <section>
            <?php foreach( $tweets as $tweet ): ?>
                <article class="tweet">
                    <blockquote>
                        <p><?= str_replace("\n", "<br/>", $tweet->text_extended) ?></p>
                        <footer>
                            <cite><a href="https://twitter.com/statuses/<?= $tweet->id ?>"><?= $tweet->user->name ?></a></cite>
                            <span>, </span>
                            <time datetime="<?= date('Y-m-d\TH:iP', $tweet->created_at_timestamp) ?>"><?= $tweet->created_at_formatted ?></time>
                            <span class="twitter_actions">
                                <a class="btn" title="tweet" href="https://twitter.com/intent/tweet?in_reply_to=<?= $tweet->id ?>">t</a>
                                <a class="btn" title="retweet" href="https://twitter.com/intent/retweet?tweet_id=<?= $tweet->id ?>">r</a>
                                <a class="btn" title="star" href="https://twitter.com/intent/favorite?tweet_id=<?= $tweet->id ?>">s</a>
                            </span>
                        </footer>
                    </blockquote>
                </article>
            <?php endforeach; ?>
            </section>
        </main>
    <?php endif; ?>
    </body>
</html>
