<?php

class MaxManagerGMFeed {

    /* Übersichtsseite, die per iframe eingebunden werden kann; notwendig für die Legende */
    private $iframeURL = 'http://www.max-manager.de/daten-extern/sw-koeln/html/speiseplaene.php?einrichtung=gummersbach&w=dw';

    /* Enpoint, mittels dem der Browser per Ajax für einen bestimmten Tag alle Speisen anfragt */
    private $endpointURL = 'http://www.max-manager.de/daten-extern/sw-koeln/html/speiseplan-render.php';

    /* Parameter, die der Anfrage mitgesendet werden, um für einen bestimmtes Datum alle Speisen zu erhalten (Filterparameter) */
    private $params = array(
        'func'  => 'make_spl',
        'locId' => 'gummersbach',
        'lang'  => 'de',
        'date'  => '' // yyyy-mm-dd
    );

    /* Pfad zur Cache-Datei, für die Zwischenspeicherung, um den Server nicht unnötig zu belasten */
    private $cacheFilepath = 'week_meals.tmp.json';

    /* Führt im späteren Verlauf den Speiseplan + Legende */
    private $weekdayMeals = null;

    /* Ermöglicht Mapping von Wochentag-Index auf Wochentag-Name */
    private $weekdayNames = array(
        'Montag',
        'Dienstag',
        'Mittwoch',
        'Donnerstag',
        'Freitag'
    );


    public function __construct() {

        /* Überprüfen, ob es evtl. eine valide Cache-Datei gibt, auf die dann gesetzt wird */
        if($this->cacheValid()) {
            $this->initFromCache();
            return;
        }

        /* Datenstruktur in ihren Grundzügen zusammensetzen */
        $this->weekdayMeals = new stdClass();
        $this->weekdayMeals->thisweek = new stdClass();
        $this->weekdayMeals->thisweek->timestampRange = null;
        $this->weekdayMeals->thisweek->weekdays       = array();

        $this->weekdayMeals->nextweek = new stdClass();
        $this->weekdayMeals->nextweek->timestampRange = null;
        $this->weekdayMeals->nextweek->weekdays       = array();

        $this->weekdayMeals->legend = null;


        /* Übersichtsseite anfragen und die Legende extrahieren */
        $mealplanHtmlRaw = file_get_contents($this->iframeURL);
        $this->weekdayMeals->legend = $this->extractMealplanLegend($mealplanHtmlRaw);

        /* Zeiträume für die aktuelle und die nächste Woche berechnen */
        $weekdayTimeranges = $this->createWeekTimeranges();

        /* Festhalten der Begin- und End-Timestamps in der $weekdayMeals-Variable,
            um sie für die Cache-Validierung nutzen zu können; weitere Einsatzzwecke sind möglich */
        $this->weekdayMeals->thisweek->timestampRange = $weekdayTimeranges->thisweekTimestampRange;
        $this->weekdayMeals->nextweek->timestampRange = $weekdayTimeranges->nextweekTimestampRange;

        /* Abfragen der Speisen für *DIESE WOCHE** */
        foreach($weekdayTimeranges->thisweek as $index => $dateStr) {
            /* Für jeden Tag der aktuellen Woche eine Anfrage an den Endpoint senden */
            $this->params['date'] = $dateStr;

            $htmlFragment = $this->request();

            $weekday = new stdClass();
            $weekday->weekdayName = $this->weekdayNames[$index];
            $weekday->date        = $dateStr;
            $weekday->meals       = $this->extractWeekdayMeals($htmlFragment, $dateStr); /* Speisen extrahieren und aufbereiten */

            $this->weekdayMeals->thisweek->weekdays[] = $weekday;
        }

        /* Abfragen der Speisen für die *NÄCHSTE WOCHE** */
        foreach($weekdayTimeranges->nextweek as $index => $dateStr) {
            /* Für jeden Tag der aktuellen Woche eine Anfrage an den Endpoint senden */
            $this->params['date'] = $dateStr;

            $htmlFragment = $this->request();

            $weekday = new stdClass();
            $weekday->weekdayName = $this->weekdayNames[$index];
            $weekday->date        = $dateStr;
            $weekday->meals       = $this->extractWeekdayMeals($htmlFragment, $dateStr); /* Speisen extrahieren und aufbereiten */

            $this->weekdayMeals->nextweek->weekdays[] = $weekday;
        }

        /* Cache aktualisieren, da der Cache nicht valide ist oder die Cache-Datei nicht existiert */
        $this->renewCache();
    }


    /**
     * Zugriff auf den Speiseplan + Legende
     *
     * @return object Speiseplan + Legende als stdClass-Objekt
     */
    public function getWeekdayMeals() {
        return $this->weekdayMeals;
    }


    /**
     * Extrahieren der Daten zu den Speisen, eines bestimmten Tages
     *
     * @param string $htmlStr HTML-Fragment
     *
     * @return object Speisen als stdClass-Objekt
     */
    private function extractWeekdayMeals($htmlStr, $dateStr) {
        /* Vorbereiten des HTML-Fragments, um mögliche Fehler beim Parsen zu umgehen */
        $htmlStr = preg_replace('/(&nbsp;)/i' , ' ', $htmlStr);
        $htmlStr = preg_replace('/&/i', '&amp;', $htmlStr);

        $htmlStr = '<html><head><meta charset="utf-8" /></head><body>' . $htmlStr . '</body></html>';

        $xml = new SimpleXMLElement($htmlStr);

        $trs = $xml->xpath('/html/body/div/table/tr');

        $meals = new stdClass();

        $currMealType = '';

        foreach($trs as $tr) {

            if($tr->attributes()->class === 'header')
                continue;

            $tds = $tr->td;

            $classVal = $tds[0]->attributes()->class;

            /* Speise-Typ */
            if(substr($classVal, 0, 2) === 'pk') {
                $currMealType = trim('' . $tds[0]);

                if(!isset($meals->$currMealType)) {
                    $meals->$currMealType = new stdClass();
                    $meals->$currMealType->article       = '';
                    $meals->$currMealType->desc          = '';
                    $meals->$currMealType->legendEntries = array();
                    $meals->$currMealType->price         = array();
                }
            }
            /* Die eigentlichen Speisedaten auf drei td-Element verstreut */
            else if(substr($classVal, 0, 4) === 'cell') {

                /* Wenn es keine drei Spalten besitzt (Speisen noch nicht gesetzt) oder
                    der Speisetyp ('Tellergericht x' usw.) bereits gesetzt wurde -> doppelte Einträge */
                if(count($tds) < 3 || !empty($meals->$currMealType->article)) {
                    // Keine Speisen für diesen Tag
                    continue;
                }

                /* Das erste td-Element wird übersprungen, da es nur ein img-Element mit einer
                    Thumbnail-Grafik enthält, und man nur an den reinen Textdaten interessiert sind */
                for($i = 1; $i < 3; $i++) {
                    switch($i) {
                        /* Speisedaten (Name u. Beschreibung) */
                        case 1:

                            $article = $tds[$i]->div->span[0];

                            /* TODO: hochstellte Zahlen besser extrahieren,
                                      ohne den Bezug zu den Speisen zu verlieren */
                            if(isset($article->sup))
                                foreach($article->sup as $sup) {
                                    $meals->$currMealType->legendEntries
                                        = array_merge($meals->$currMealType->legendEntries,
                                                      array_map('trim', explode(',', $sup))   );
                                }

                            $desc = $tds[$i]->div->span[1];

                            /* TODO: hochstellte Zahlen besser extrahieren,
                                      ohne den Bezug zu den Speisen zu verlieren */
                            if(isset($desc->sup))
                                foreach($desc->sup as $sup) {
                                    $meals->$currMealType->legendEntries
                                        = array_merge($meals->$currMealType->legendEntries,
                                                      array_map('trim', explode(',', $sup))   );
                                }

                            $meals->$currMealType->article = html_entity_decode('' . $article);
                            $meals->$currMealType->desc    = html_entity_decode('' . $desc);

                            break;

                        /* Preis */
                        case 2:
                            $meals->$currMealType->price =
                                array_map('trim', explode(' / ', '' . $tds[$i]));

                            if(empty($meals->$currMealType->price[0]))
                                $meals->$currMealType->price = false;

                            break;
                    }
                }
            }
        }

        return $meals;
    }

    /**
     * Extrahieren der Legende aus der Speisekartenübersicht
     *
     * @param string $htmlStr HTML-Dokument
     *
     * @return object Legende der Speisekarte als stdClass-Objekt
     */
    private function extractMealplanLegend($htmlStr) {
        /* Vorbereiten des HTML-Fragments, um mögliche Fehler beim Parsen zu umgehen */
        $htmlStr = preg_replace('/(&nbsp;)/i' , ' ', $htmlStr);
        $htmlStr = preg_replace('/&/i', '&amp;', $htmlStr);

        $xml = new SimpleXMLElement($htmlStr);

        $obj = new stdClass();
        $obj->main        = array();
        $obj->additives   = array();
        $obj->allergenics = array();
        $obj->others      = array();

        $legendDiv = $xml->body->div[4];

        $obj->main[] = html_entity_decode('' . $legendDiv->p);

        /* Zusatzstoffe / Additives */
        foreach($legendDiv->div[0]->ul->li as $li) {
            list($num, $desc) = explode(' = ', ''.$li);
            $obj->additives[$num] = $desc;
        }

        /* Allergene / Allergenics */
        foreach($legendDiv->div[1]->ul[0]->li as $li) {
            list($num, $desc) = explode(' = ', ''.$li);
            $obj->allergenics[$num] = $desc;
        }

        /* Sonstiges / Others */
        foreach($legendDiv->div[1]->ul[1]->li as $li) {
            list($num, $desc) = explode(' = ', ''.$li);
            $obj->others[$num] = $desc;
        }

        return $obj;
    }


    /**
     * HTTP-POST-Request vorbereiten und absetzen
     *
     * @param array $params Assoziatives Array mit string-string-Paaren
     *
     * @return string HTML-Dokument bzw. -Fragment
     */
    private function request($params = null) {
        if(is_null($params))
            $params = $this->params;

        $options = array(
            'http' => array(
                'method' => "POST",
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($params)
            )
        );

        $context = stream_context_create($options);

        return file_get_contents($this->endpointURL, false, $context);
    }

    /**
     * Berechnen und Zusammensetzen von Zeiträumen, indem für diese und die nächste Woche
     *  alle Tage von Montag bis Freitag die Datumsangabe konstruiert wird
     *
     * @return object stdClass-Objekt mit den Eigenschaften
     *                  `thisweek_timestamp_range` mit Beginn- und End-Timespamps der aktuellen Woche,
     *                  `nextweek_timestamp_range` mit Beginn- und End-Timespamps der nächsten Woche,
     *                  `thisweek` als Array mit allen Datumsangaben dieser Woche und
     *                  `nextweek` als Array mit allen Datumsangaben der nächsten Woche
     */
    private function createWeekTimeranges() {
        $dtThisweek = new DateTime('now');
        $dtThisweek->setTime(1, 0, 0);

        /* Auf den Anfang der Woche setzen */
        $currWeekdayNum = intval($dtThisweek->format('N'));
        $dtThisweek->sub(new DateInterval('P' . ( $currWeekdayNum -1 ) . 'D'));

        /* Der Anfang der nächsten Woche ist der Anfang dieser Woche + 7 Tage */
        $dtNextweek = new DateTime();
        $dtNextweek->setTimestamp($dtThisweek->getTimestamp());
        $dtNextweek->add(new DateInterval('P7D'));

        $diff1day = new DateInterval('P1D');

        $thisweekTimestampRange = array();
        $nextweekTimestampRange = array();

        /* Den Anfang der beiden Wochen jeweils als Timestamp festhalten (Erstes Element im timestamp_range-Array) */
        $thisweekTimestampRange[] = $dtThisweek->getTimestamp();
        $nextweekTimestampRange[] = $dtNextweek->getTimestamp();

        $thisweekRangeFormattedDateArr = array();
        $nextweekRangeFormattedDateArr = array();

        /* Nur von Montag bis Freitag; Beide Wochen werden parallel durchlaufen, um nur eine Schleife zu nutzen */
        for($dayRunner = 0; $dayRunner < 5; $dayRunner++) {
            /* Datumsangabe der Form yyyy-mm-dd für den aktuellen durchlaufenden Wochentag erzeugen */
            $thisweekRangeFormattedDateArr[] = $dtThisweek->format('Y-m-d');
            $nextweekRangeFormattedDateArr[] = $dtNextweek->format('Y-m-d');

            /* Inkrementieren um einen Tag */
            $dtThisweek->add($diff1day);
            $dtNextweek->add($diff1day);
        }

        /* Das Ende der beiden Wochen jeweils als Timestamp festhalten (Zweite und letzte Element im timestamp_range-Array) */
        $thisweekTimestampRange[] = $dtThisweek->getTimestamp();
        $nextweekTimestampRange[] = $dtNextweek->getTimestamp();

        $obj = new stdClass();

        $obj->thisweekTimestampRange = $thisweekTimestampRange;
        $obj->nextweekTimestampRange = $nextweekTimestampRange;

        $obj->thisweek = $thisweekRangeFormattedDateArr;
        $obj->nextweek = $nextweekRangeFormattedDateArr;

        return $obj;
    }


    /**
     * Überprüfen ob der Cache valide ist, indem überprüft wird, ob eine Cache-Datei existiert
     *  und ob ein Wochenwechsel stattfand (Aktuelle Woche war zum Zeitpunkt der Cache-Aktualisierung die 'nächste Woche')
     *
     * @return bool Status über die Validität des Cache
     */
    private function cacheValid() {
        if(!file_exists($this->cacheFilepath))
            return false;

        $weekdayMeals = json_decode(file_get_contents($this->cacheFilepath));

        $now = new DateTime('now');
        if($now->getTimestamp() > $weekdayMeals->nextweek->timestampRange[0])
            return false;

        return true;
    }

    /**
     * Initialisierung der Speisekarten-Datenstruktur auf Basis der Cache-Datei
     */
    private function initFromCache() {
        $this->weekdayMeals = json_decode(file_get_contents($this->cacheFilepath));
    }

    /**
     * Cache-Datei aktualisieren oder, sofern sie nicht exisitert, sie erzeugen
     */
    private function renewCache() {
        file_put_contents($this->cacheFilepath, json_encode($this->weekdayMeals, JSON_PRETTY_PRINT));
    }
}

?>
