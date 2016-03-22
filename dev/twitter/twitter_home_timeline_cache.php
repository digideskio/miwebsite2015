<?php


require_once('../../assets/lib/twitter-api-php/TwitterAPIExchange.php');


class TwitterHomeTimelineCache {

    /* Für TwitterAPIExchange benötigt -> buildOAuth */
    const GET_METHOD  = 'GET';

    private $settings = array(
        'screen_name'       => '',
        'skip_user'         => array(),
        'accepted_hashtags' => array(),

        'fetch_count'            => 20, // Anzahl Tweets, pro Anfrage
        'keep_tweets_count'      => 100, // Anzahl Tweets, die maximal im Cache gehalten werden sollen
        'cache_validity_period'  => 10 * 60, // Sekunden
        'autolink_attributes'    => array(),
        'stopwordlist_filepaths' => array(),

        /* Von TwitterAPIExchange benötigt */
        'oauth_access_token'        => '',
        'oauth_access_token_secret' => '',
        'consumer_key'              => '',
        'consumer_secret'           => ''
    );

    /* API-URL um die Tweets anzufragen, die in der Home Timeline ausgegeben werden */
    private $apiHomeTimelineURL = 'https://api.twitter.com/1.1/statuses/home_timeline.json';
    /* Assoziatives Array um GET-Parameter zu halten */
    private $getField           = array();

    private $cacheFilepath = 'tweets.tmp.json';
    private $cache = null;

    private $stopwords = null;

    private $twitterAPIExchangeObj;


    function __construct($settings) {
        $this->settings = array_merge($this->settings, $settings);

        /* Standardwerte für die GET-Parameter setzen */
        $this->getField = array(
            'screen_name' => $this->settings["screen_name"],
            'count'       => $this->settings["fetch_count"]
        );

        /* Zu überspringende Usernames und akzeptierte Hashtags aufsplitten,
            sofern sie als String anstatt Array gegeben sind */
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

        /* Bereits bezogene Tweets aus dem Cache beziehen, sofern einer zuvor angelegt wurde */
        $this->initCache();
    }


    /**
     * Holen von Tweets aus dem Cache oder sofern dieser nicht mehr aktuell ist,
     *    direkt über die Twitter-API
     *
     * @param int $count Anzahl an Tweets, die zurückgegeben werden sollen
     *
     * @return array|object Ein Array mit Tweet-Objekten (stdClass), oder ein
     *                      Fehlerobjekt mit Twitter-Error-Code und Error-Nachricht
     */
    function getTweets($count = 10) {
        $now = new DateTime('now');

        /* Um nicht über Twitters Rate-Limit zu kommen */
        if(   ($now->getTimestamp() - $this->cache->last_check_timestamp)
            < $this->settings['cache_validity_period'])
            return array_slice($this->cache->tweets, 0, $count);

        $lastId = -1;

        /* Nicht mehr Tweets anfragen, als man am Ende im Cache hält */
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


    /**
     * Erzeugung einer Liste aller Wörter (aller Tweets im Cache),
     *  samt Anzahl ihrer Vorkommnisse;
     * Vor jeder Konstruktion der Wortliste, wird versucht der
     *  Cache aktuell zu halten. Konnte der Cache nicht aktuell
     *  gehalten werden bzw. keine neuen Tweets abgefragt werden,
     *  wird die Wortliste auf Basis des nicht aktualisierten Caches
     *  konstruiert.
     *
     * @return array Wörter + Anzahl der Vokommnisse
     */
    function getWordlist() {
        /* Aktualisierung des Caches anstoßen */
        $this->getTweets();
    
        $wordlist = array();

        if(is_null($this->stopwords)) {
            $this->stopwords = array();

            foreach($this->settings['stopwordlist_filepaths'] as $filepath) {
                if(!file_exists($filepath))
                    continue;

                $currFileStopwords = file($filepath);
                $currFileStopwords = array_map('trim', $currFileStopwords);

                $this->stopwords = array_merge($this->stopwords, $currFileStopwords);
            }

            $this->stopwords = array_unique($this->stopwords);
        }

        foreach($this->cache->tweets as $tweet) {

            $hashtags = $tweet->entities->hashtags;
            $hashtags = array_map(function($hashtag) { return $hashtag->text; }, $hashtags);

            $text = $tweet->text_extended;

            $text = strtolower($text);
            $text = preg_replace('/(<a .*?\/a>)/', '', $text);
            $text = preg_replace('/[^\w\'ßäöü]+/u', ' ', $text);
            $splitText = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

            $splitText = array_map('strtolower', $splitText);

            $words = array_merge($splitText, $hashtags);

            foreach($words as $word) {
                if(in_array($word, $this->stopwords))
                    continue;

                if(!isset($wordlist[$word]))
                    $wordlist[$word] = 1;

                $wordlist[$word]++;
            }
        }

        return $wordlist;
    }


    /**
     * Tweets anhand von Parametern filtern (Tweets von bestimmten Usern überspringen;
     *    nur Tweets mit bestimmten Hashtags) und um hilfreiche Eigenschaften erweitern
     *    (Unix-Timestamp, vorformartiertes Datum und URIs in Links umwandeln)
     *
     * @param array $tweets Array mit noch unangetasteten Tweets (stdClass-Objekte)
     *
     * @return array Array mit gefilterten und erweiterten Tweets
     */
    function filterAndExtendTweets($tweets) {

        $filteredTweets = array();

        foreach($tweets as $tweet) {
            if(in_array($tweet->user->screen_name, $this->settings['skip_user']))
                continue;

            /* Sofern akzeptierte Hashtags gegeben sind (mindestens einer),
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

            $tweet->text_extended        = html_entity_decode($tweet->text);
            $tweet->text_extended        = $this->autolink($tweet->text_extended);
            $tweet->created_at_formatted = $this->convertDate($tweet->created_at);
            $tweet->created_at_timestamp = $this->convertDateToTimestamp($tweet->created_at);
            $tweet->text_extended        = $this->autolinkHashtags($tweet->text_extended);
            $tweet->text_extended        = $this->autolinkMentions($tweet->text_extended);

            $filteredTweets[] = $tweet;
        }

        return $filteredTweets;
    }


    /**
     * In Twitter-Nachrichtentexte entahltene URIs in Links umwandeln
     *    -> HTML-anchor-Element
     *
     * @param string $str Tweet-Nachrichtentext
     *
     * @return string Tweet-Nachrichtentext mit Links
     */
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


    /**
     * In Twitter-Nachrichtentexte entahltene Hashtags in Links umwandeln
     *    -> https://twitter.com/hashtag/<hashtag>
     *
     * @param string $str Tweet-Nachrichtentext
     *
     * @return string Tweet-Nachrichtentext mit Links
     */
    private function autolinkHashtags($str) {
        $result = preg_replace( '/#([\wäöüß]+)/',
                                '<a class="hashtag" href="https://twitter.com/hashtag/$1">#$1</a>',
                                $str);

        if(empty($result))
            return $str;

        return $result;
    }


    /**
     * In Twitter-Nachrichtentexte entahltene Mentions (@screen_name) in Links umwandeln
     *    -> https://twitter.com/<screen_name>
     *
     * @param string $str Tweet-Nachrichtentext
     *
     * @return string Tweet-Nachrichtentext mit Links
     */
    private function autolinkMentions($str) {
        $result = preg_replace( '/@([\w]+)/',
                                '<a class="mention" href="https://twitter.com/$1">@$1</a>',
                                $str);

        if(empty($result))
            return $str;

        return $result;
    }


    /**
     * Konvertieren eines Tweet-Erstellungszeitpunkts in ein Datumsstrings
     *    anhand eines gegebenen Formats
     *
     * @param int $date RFC-Datumsstring
     * @param string $format Datumsformat
     *
     * @return string Erstellungsdatum im Zielformat
     */
    private function convertDate($date, $format = 'd.m.Y - H:i') {
        $date = new DateTime($date);
        return $date->format($format);
    }


    /**
     * Konvertieren eines Tweet-Erstellungszeitpunkts in ein UNIX-Timestamp
     *
     * @param int $date RFC-Datumsstring
     *
     * @return int UNIX-Timestamp
     */
    private function convertDateToTimestamp($date) {
        $date = new DateTime($date);
        return $date->getTimestamp();
    }


    /**
     * Cache-Variable setzen;
     *    entweder auf Basis eines bestehenden Caches, oder mit initialen Standardwerten
     */
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
