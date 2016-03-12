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
    private $cache_filepath = 'week_meals.tmp.json';

    /* Führt im späteren Verlauf den Speiseplan + Legende */
    private $weekday_meals = null;

    /* Ermöglicht Mapping von Wochentag-Index auf Wochentag-Name */
    private $weekday_names = array(
        'Montag',
        'Dienstag',
        'Mittwoch',
        'Donnerstag',
        'Freitag'
    );


    public function __construct() {
        
        /* Überprüfen, ob es evtl. eine valide Cache-Datei gibt, auf die dann gesetzt wird */
        if($this->cache_valid()) {
            $this->init_from_cache();
            return;
        }
        
        /* Datenstruktur in ihren Grundzügen zusammensetzen */
        $this->weekday_meals = new stdClass();
        $this->weekday_meals->thisweek = new stdClass();
        $this->weekday_meals->thisweek->timestamp_range = null;
        $this->weekday_meals->thisweek->weekdays        = array();
        
        $this->weekday_meals->nextweek = new stdClass();
        $this->weekday_meals->nextweek->timestamp_range = null;
        $this->weekday_meals->nextweek->weekdays        = array();
        
        $this->weekday_meals->legend = null;
        
        
        /* Übersichtsseite anfragen und die Legende extrahieren */
        $mealplan_html_raw = file_get_contents($this->iframeURL);
        $this->weekday_meals->legend = $this->extract_mealplan_legend($mealplan_html_raw);
        
        /* Zeiträume für die aktuelle und die nächste Woche berechnen */
        $weekday_timeranges = $this->create_week_timeranges();
        
        /* Festhalten der Begin- und End-Timestamps in der $weekday_meals-Variable,
            um sie für die Cache-Validierung nutzen zu können; weitere Einsatzzwecke sind möglich */
        $this->weekday_meals->thisweek->timestamp_range = $weekday_timeranges->thisweek_timestamp_range;
        $this->weekday_meals->nextweek->timestamp_range = $weekday_timeranges->nextweek_timestamp_range;
        
        /* Abfragen der Speisen für *DIESE WOCHE** */
        foreach($weekday_timeranges->thisweek as $index => $date_str) {
            /* Für jeden Tag der aktuellen Woche eine Anfrage an den Endpoint senden */
            $this->params['date'] = $date_str;
            
            $html_fragment = $this->request();
            
            $weekday = new stdClass();
            $weekday->weekday_name = $this->weekday_names[$index];
            $weekday->date = $date_str;
            $weekday->meals = $this->extract_weekday_meals($html_fragment); /* Speisen extrahieren und aufbereiten */
            
            $this->weekday_meals->thisweek->weekdays[] = $weekday;
        }
        
        /* Abfragen der Speisen für *NÄCHSTE WOCHE** */
        foreach($weekday_timeranges->nextweek as $index => $date_str) {
            /* Für jeden Tag der aktuellen Woche eine Anfrage an den Endpoint senden */
            $this->params['date'] = $date_str;
            
            $html_fragment = $this->request();
            
            $weekday = new stdClass();
            $weekday->weekday_name = $this->weekday_names[$index];
            $weekday->date = $date_str;
            $weekday->meals = $this->extract_weekday_meals($html_fragment); /* Speisen extrahieren und aufbereiten */
            
            $this->weekday_meals->nextweek->weekdays[] = $weekday;
        }
        
        /* Cache aktualisieren, da der Cache nicht valide ist oder die Cache-Datei nicht existiert */
        $this->renew_cache();
    }


    /**
     * Zugriff auf den Speiseplan + Legende
     *
     * @return object Speiseplan + Legende als stdClass-Objekt
     */
    public function get_weekday_meals() {
        return $this->weekday_meals;
    }


    /**
     * Extrahieren der Daten zu den Speisen, eines bestimmten Tages
     *
     * @param string $html_str HTML-Fragment
     *
     * @return object Speisen als stdClass-Objekt
     */
    private function extract_weekday_meals($html_str) {
        /* Vorbereiten des HTML-Fragments, um mögliche Fehler beim Parsen zu umgehen */
        $html_str = preg_replace('/(&nbsp;)/i' , ' ', $html_str);
        $html_str = preg_replace('/&/i', '&amp;', $html_str);
        
        $html_str = '<html><head><meta charset="utf-8" /></head><body>' . $html_str . '</body></html>';
        
        $xml = new SimpleXMLElement($html_str);
        
        $trs = $xml->xpath('/html/body/div/table/tr');
        
        $meals = new stdClass();
        
        $curr_meal_type = '';
        
        foreach($trs as $tr) {
        
            if($tr->attributes()->class === 'header')
                continue;
            
            $tds = $tr->td;
            
            $class_val = $tds[0]->attributes()->class;
            
            /* Speise-Typ */
            if(substr($class_val, 0, 2) === 'pk') {
                $curr_meal_type = '' . $tds[0];
                
                if(!isset($meals->$curr_meal_type)) {
                    $meals->$curr_meal_type = new stdClass();
                    $meals->$curr_meal_type->article        = '';
                    $meals->$curr_meal_type->desc           = '';
                    $meals->$curr_meal_type->legend_entries = array();
                    $meals->$curr_meal_type->price          = array();
                }
            }
            /* Die eigentlichen Speisedaten auf drei td-Element verstreut */
            else if(substr($class_val, 0, 4) === 'cell') {
            
                /* Das erste td-Element wird übersprungen, da es nur ein img-Element mit einer
                    Thumbnail-Grafik enthält, und man nur an den reinen Textdaten interessiert sind */
                for($i = 1; $i < 3; $i++) {
                    switch($i) {
                        /* Speisedaten (Name u. Beschreibung) */
                        case 1:
                            
                            $article = $tds[$i]->div->span[0];
                            
                            /* TODO: hochstellte Zahlen besser extrahieren, ohne den Bezug zu den Speisen zu verlieren */
                            if(isset($article->sup))
                                foreach($article->sup as $sup) {
                                    $meals->$curr_meal_type->legend_entries
                                        = array_merge($meals->$curr_meal_type->legend_entries, array_map('trim', explode(',', $sup)));
                                }
                            
                            $desc = $tds[$i]->div->span[1];
                            
                            /* TODO: hochstellte Zahlen besser extrahieren, ohne den Bezug zu den Speisen zu verlieren */
                            if(isset($desc->sup))
                                foreach($desc->sup as $sup) {
                                    $meals->$curr_meal_type->legend_entries
                                        = array_merge($meals->$curr_meal_type->legend_entries, array_map('trim', explode(',', $sup)));
                                }
                            
                            $meals->$curr_meal_type->article = html_entity_decode('' . $article);
                            $meals->$curr_meal_type->desc    = html_entity_decode('' . $desc);
                            
                            break;
                        
                        /* Preis */
                        case 2:
                            $meals->$curr_meal_type->price = array_map('trim', explode(' / ', '' . $tds[$i]));
                            
                            if(empty($meals->$curr_meal_type->price[0]))
                                $meals->$curr_meal_type->price = false;
                            
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
     * @param string $html_str HTML-Dokument
     *
     * @return object Legende der Speisekarte als stdClass-Objekt
     */
    private function extract_mealplan_legend($html_str) {
        /* Vorbereiten des HTML-Fragments, um mögliche Fehler beim Parsen zu umgehen */
        $html_str = preg_replace('/(&nbsp;)/i' , ' ', $html_str);
        $html_str = preg_replace('/&/i', '&amp;', $html_str);
        
        $xml = new SimpleXMLElement($html_str);
        
        $obj = new stdClass();
        $obj->main        = array();
        $obj->additives   = array();
        $obj->allergenics = array();
        $obj->others      = array();
        
        $legend_div = $xml->body->div[4];
        
        $obj->main[] = html_entity_decode('' . $legend_div->p);
        
        /* Zusatzstoffe / Additives */
        foreach($legend_div->div[0]->ul->li as $li) {
            list($num, $desc) = explode(' = ', ''.$li);
            $obj->additives[$num] = $desc;
        }
        
        /* Allergene / Allergenics */
        foreach($legend_div->div[1]->ul[0]->li as $li) {
            list($num, $desc) = explode(' = ', ''.$li);
            $obj->allergenics[$num] = $desc;
        }
        
        /* Sonstiges / Others */
        foreach($legend_div->div[1]->ul[1]->li as $li) {
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
    private function create_week_timeranges() {
        $dt_thisweek = new DateTime('now');
        $dt_thisweek->setTime(1, 0, 0);
        
        /* Auf den Anfang der Woche setzen */
        $curr_weekday_num = intval($dt_thisweek->format('N'));
        $dt_thisweek->sub(new DateInterval('P' . ( $curr_weekday_num -1 ) . 'D'));
        
        /* Der Anfang der nächsten Woche ist der Anfang dieser Woche + 7 Tage */
        $dt_nextweek = new DateTime();
        $dt_nextweek->setTimestamp($dt_thisweek->getTimestamp());
        $dt_nextweek->add(new DateInterval('P7D'));
        
        $diff1day = new DateInterval('P1D');
        
        $thisweek_timestamp_range = array();
        $nextweek_timestamp_range = array();
        
        /* Den Anfang der beiden Wochen jeweils als Timestamp festhalten (Erstes Element im timestamp_range-Array) */
        $thisweek_timestamp_range[] = $dt_thisweek->getTimestamp();
        $nextweek_timestamp_range[] = $dt_nextweek->getTimestamp();
        
        $thisweek_range_formatted_date_arr = array();
        $nextweek_range_formatted_date_arr = array();
        
        /* Nur von Montag bis Freitag; Beide Wochen werden parallel durchlaufen, um nur eine Schleife zu nutzen */
        for($day_runner = 0; $day_runner < 5; $day_runner++) {
            /* Datumsangabe der Form yyyy-mm-dd für den aktuellen durchlaufenden Wochentag erzeugen */
            $thisweek_range_formatted_date_arr[] = $dt_thisweek->format('Y-m-d');
            $nextweek_range_formatted_date_arr[] = $dt_nextweek->format('Y-m-d');
            
            /* Inkrementieren um einen Tag */
            $dt_thisweek->add($diff1day);
            $dt_nextweek->add($diff1day);
        }
        
        /* Das Ende der beiden Wochen jeweils als Timestamp festhalten (Zweite und letzte Element im timestamp_range-Array) */
        $thisweek_timestamp_range[] = $dt_thisweek->getTimestamp();
        $nextweek_timestamp_range[] = $dt_nextweek->getTimestamp();
        
        $obj = new stdClass();
        
        $obj->thisweek_timestamp_range = $thisweek_timestamp_range;
        $obj->nextweek_timestamp_range = $nextweek_timestamp_range;
        
        $obj->thisweek = $thisweek_range_formatted_date_arr;
        $obj->nextweek = $nextweek_range_formatted_date_arr;
        
        return $obj;
    }


    /**
     * Überprüfen ob der Cache valide ist, indem überprüft wird, ob eine Cache-Datei existiert
     *  und ob ein Wochenwechsel stattfand (Aktuelle Woche war zum Zeitpunkt der Cache-Aktualisierung die 'nächste Woche')
     *
     * @return bool Status über die Validität des Cache
     */
    private function cache_valid() {
        if(!file_exists($this->cache_filepath))
            return false;
        
        $weekday_meals = json_decode(file_get_contents($this->cache_filepath));
        
        $now = new DateTime('now');
        if($now->getTimestamp() > $weekday_meals->nextweek->timestamp_range[0])
            return false;
        
        return true;
    }

    /**
     * Initialisierung der Speisekarten-Datenstruktur auf Basis der Cache-Datei
     */
    private function init_from_cache() {
        $this->weekday_meals = json_decode(file_get_contents($this->cache_filepath));
    }

    /**
     * Cache-Datei aktualisieren oder, sofern sie nicht exisitert, sie erzeugen
     */
    private function renew_cache() {
        file_put_contents($this->cache_filepath, json_encode($this->weekday_meals, JSON_PRETTY_PRINT));
    }
}

?>
