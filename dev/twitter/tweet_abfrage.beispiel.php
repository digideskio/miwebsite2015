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
    //'accepted_hashtags' => getOrElse( explode(',', getenv('TWITTER_ACCEPTED_HASHTAGS')), array('mikoeln,medieninformatik')), // usw.


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

if(isset($tweets->errors)) {
    foreach($tweets->errors as $error) {
        ?>
        <p><b>Error <?= $error->code ?>:</b> <?= $error->message ?></p>
        <?php
    }

    ?>
    <p>Wahrscheinlich wurden im Skript nicht die nötigen TOKENS, KEYS und SECRETS bzw. die Umgebungsvariablen gesetzt</p>
    <?php

    die();
}

foreach($tweets as $tweet) {
    ?>
    <a style="display:block;" href="https://twitter.com/statuses/<?= $tweet->id ?>">
        <blockquote>
            <p><?= str_replace("\n", "<br/>", $tweet->text) ?></p>
            <footer><cite><?= $tweet->user->screen_name ?></cite></footer>
        </blockquote>
    </a>
    <?php
}
