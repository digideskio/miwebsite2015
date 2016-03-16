<?php


require_once('../../assets/lib/twitter-api-php/TwitterAPIExchange.php');


class TwitterHomeTimelineCache {

    const GET_METHOD  = 'GET';

    private $settings = array(
        'screen_name'       => '',
        'skip_user'         => array(),
        'accepted_hashtags' => array(),

        'fetch_count'           => 20, // Anzahl Tweets, pro Anfrage
        'keep_tweets_count'     => 50, // Anzahl Tweets, die maximal im Cache gehalten werden sollen
        'cache_validity_period' => 10 * 60, // Sekunden
        'autolink_attributes'   => array(),

        /* Von TwitterAPIExchange benötigt */
        'oauth_access_token'        => '',
        'oauth_access_token_secret' => '',
        'consumer_key'              => '',
        'consumer_secret'           => ''
    );

    private $apiHomeTimelineURL = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
    private $getField           = array();

    private $cacheFilepath = 'tweets.tmp.json';
    private $cache = null;

    private $twitterAPIExchangeObj;


    function __construct($settings) {
        $this->settings = array_merge($this->settings, $settings);

        $this->getField = array(
            'screen_name' => $this->settings["screen_name"],
            'count'       => $this->settings["fetch_count"]
        );

        if(is_string($this->settings['skip_user'])) {
            $this->settings['skip_user'] =
                explode(',', $this->settings['skip_user']);
        }
        $this->settings['skip_user'] = array_map('trim', $this->settings['skip_user']);

        if(is_string($this->settings['accepted_hashtags'])) {
            $this->settings['accepted_hashtags'] =
                explode(',', $this->settings['accepted_hashtags']);
        }
        $this->settings['accepted_hashtags'] = array_map('trim', $this->settings['accepted_hashtags']);

        $this->settings['accepted_hashtags'] =
              array_map('strtolower', $this->settings['accepted_hashtags']);

        $this->twitterAPIExchangeObj = new TwitterAPIExchange($this->settings);

        $this->initCache();
    }


    function getTweets($count = 10) {
        $now = new DateTime('now');

        /* Um nicht über Twitters Rate-Limit zu kommen */
        if(   ($now->getTimestamp() - $this->cache->last_check_timestamp)
            < $this->settings['cache_validity_period'])
            return array_slice($this->cache->tweets, 0, $count);

        $lastId = -1;

        if($count > $this->settings['keep_tweets_count'])
            $count = $this->settings['keep_tweets_count'];

        $getField = $this->getField;

        /* ID des aktuellsten Tweets im Cache festhalten */
        if(count($this->cache->tweets) > 0) {
            $topTweet = $this->cache->tweets[0];
            $lastId = $topTweet->id;
        }

        if($lastId > 0) {
            /* Sofern aus dem aktuellsten Tweet (sofern vorhanden),
                eine ID entnommen werden konnte, wird diese bei der Anfrage mitgereicht,
                um keine Tweets mehr anzufragen, die man vllt. schon bereits im Cache hat */
            $getField['last_id'] = $lastId;

            /* Werden mehr Tweets angefragt, als die Summe der standardmäßig abgefragten
                Tweets und der Tweets im Cache, wird der count-Parameter entsprechend gesetzt */
            if($count > ($getField['count'] + count($this->cache->tweets)))
                $getField['count'] = $count;
        }
        else {
            /* Nicht mehr Tweets anfragen, als man maximal im Cache halten möchte */
            $getField['count'] = $this->settings['keep_tweets_count'];
        }

        /* TwitterAPIExchange die neuen Parameter bekannt geben */
        $this->twitterAPIExchangeObj->setGetfield('?' . http_build_query($getField));

        /* Tweets abfragen */
        $results = $this->twitterAPIExchangeObj
                              ->buildOauth($this->apiHomeTimelineURL, self::GET_METHOD)
                              ->performRequest();
        $results = json_decode($results);

        if(isset($results->errors))
          return $results;

        $tweets = $this->filterAndExtendTweets($results);

        /* Die aktuellsten Tweets vorne anfügen */
        $this->cache->tweets = array_merge($tweets, $this->cache->tweets);
        /* Um den Cache nicht unnötig gro0 werden zu lassen,
            wird die gegebene maximale Cache-Größe eingehalten */
        $this->cache->tweets = array_slice($this->cache->tweets, 0,
                                           $this->settings['keep_tweets_count']);

        $this->cache->last_check_timestamp = $now->getTimestamp();

        file_put_contents($this->cacheFilepath, json_encode($this->cache, JSON_PRETTY_PRINT));

        return array_slice($this->cache->tweets, 0, $count);
    }


    function filterAndExtendTweets($tweets) {

        $filteredTweets = array();

        foreach($tweets as $tweet) {
            if(in_array($tweet->user->screen_name, $this->settings['skip_user']))
                continue;

            /* Sofern akzeptierte Hastags gegeben sind (mindestens einer),
                dann sind nur Tweets erlaubt, die die entsprechenden Hashtags enthalten  */
            if(count($this->settings['accepted_hashtags']) > 0) {
                $tweetHashtags = array_map(function($hashtag) { return strtolower($hashtag->text); },
                                           $tweet->entities->hashtags);

                /* Wenn Tweet keine akzeptierten Hashtags enthält, wird dieser übersprungen */
                $matchingHashtags = array_intersect($tweetHashtags,
                                                    $this->settings['accepted_hashtags']);
                if(count($matchingHashtags) === 0) {
                    continue;
                }
            }

            $tweet->autolinked_text      = $this->autolink($tweet->text);
            $tweet->formatted_created_at = $this->convertDate($tweet->created_at);

            $filteredTweets[] = $tweet;
        }

        return $filteredTweets;
    }


    private function autolink($str) {
        $attrs = '';

        foreach ($this->settings['autolink_attributes'] as $attribute => $value)
            $attrs .= " {$attribute}=\"{$value}\"";

        $str = ' ' . $str;
        $str = preg_replace('`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i','$1<a href="$2"'.$attrs.'>$2</a>',$str);
        $str = substr($str, 1);
        $str = preg_replace('`href=\"www`','href="http://www',$str);

        return $str;
    }


    private function convertDate($date, $dateFormat = 'd.m.Y') {
      $months = array();

      for($x=0; $x<12; $x++) {
        $months[$x] = date('M', mktime(0, 0, 0, $x, 1, 2000));
      }
      #Sun Jan 20 20:18:25 +0000 2013
      list($day_name, $month, $day, $date, $timecode, $year) = explode(' ', $date);
      $month = array_search($month,$months);

      return date($dateFormat, mktime(0, 0, 0, $month, $day, $year)) . " - " .
                preg_replace("=:..$=", "", $date);
    }

    private function initCache() {
        if(file_exists($this->cacheFilepath)) {
            $cacheContentRaw = file_get_contents($this->cacheFilepath);
            $this->cache = json_decode($cacheContentRaw);
        }
        else {
            $this->cache = new stdClass();
            $this->cache->last_check_timestamp = 0;
            $this->cache->tweets               = array();
        }
    }
}
