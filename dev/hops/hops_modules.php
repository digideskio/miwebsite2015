<?php


class HOPSModules {

    private $moduleBaseUrl = "http://www.medieninformatik.th-koeln.de/dev/api-bridge.php";
    private $params = array(
        'program'  => 'MI_B',
        'emphasis' => NULL
    );

    private $moduleValueFieldnames = array(
        "ANZAHL_SWS"                    => "num",
        "HOERERZAHL"                    => "num",
        "KREDITPUNKTE"                  => "num",
        "AKTIV"                         => "bool",
        "FB_NR"                         => "num",
        "MODUL_ID"                      => "num",
        "MODULCREDITS"                  => "num",
        "MODULDAUER"                    => "num",
        "MODULSWS"                      => "num",
        "MODULAUFWAND"                  => "num",
        "MODULAUFWAND_KONTAKTZEIT_AQAS" => "num",
        "MODULSWS_VORLESUNG"            => "num",
        "MODULAUFWAND_VORLESUNG"        => "num",
        "MODULSWS_PRAKTIKUM"            => "num",
        "MODULAUFWAND_PRAKTIKUM"        => "num",
        "MODULSWS_UEBUNG"               => "num",
        "MODULAUFWAND_UEBUNG"           => "num",
        "MODULSWS_SEMINAR"              => "num",
        "MODULAUFWAND_SEMINAR"          => "num",
        "MODULAUFWAND_SELBSTSTUDIUM"    => "num",
        "GRUPPENGROESSE"                => "num",
        "AUFWAND"                       => "num"
    );

    private $modules = array();


    function __construct($filterParams = array()) {
        /* Modulübersicht anfordern */
        $this->params             = array_merge($this->params, $filterParams);
        $moduleOverviewJSONString = $this->request('modules', $this->params);
        $moduleOverview           = json_decode($moduleOverviewJSONString);

        /* Modul-IDs in Array sammeln */
        $mIDs = array();
        foreach($moduleOverview->results as $moduleResult) {
            $mIDs[] = $moduleResult->ID;
        }
        $mIDs = array_unique($mIDs);

        /* Moduldetails anfordern */
        foreach($mIDs as $mID) {
            $paramMID = array("mid" => $mID);
            $moduleDetailsJSONString = $this->request('details', $paramMID);

            /* JSON extrahieren und säubern */
            $moduleDetailsJSONString = $this->extractJSONStringFromHTMLBody($moduleDetailsJSONString);
            $moduleDetailsJSONString = $this->sanitizeJSONString($moduleDetailsJSONString);

            $module = json_decode($moduleDetailsJSONString);

            if(is_array($module)) {
                if(count($module) === 1)
                    $module = $module[0];
                else
                    continue;
            }

            /* Da alle Werte vom Typ String und nicht immer atomar sind,
                werden sie aufgetrennt und in ihren eigentlichen Typ konvertiert */
            $module = $this->parseModuleDozenten($module);
            $module = $this->parseModuleValues($module);
            $module = $this->parseModuleCourseAndSemester($module);

            $this->modules[$mID] = $module;
        }
    }

    /**
      * Rückgabe aller Module als Array
      *
      * @return Array mit Modul-Objekten
      */
    public function getModules() {
        return $this->modules;
    }

    /**
      * HTTP-Request konstruieren (Query-Parameter) und absetzen
      *
      * @param string $modus Art der zu beziehenden Modul-Daten
      * @param array $params Filterparameter (Key-Value-Pairs)
      *
      * @return string Body des HTTP-Response
      */
    private function request($modus, $params) {

        $paramsArr = array('modus=' . $modus);

        foreach($params as $key => $val) {
            if(!is_null($val))
                $paramsArr[] = $key . "=" . $val;
        }

        $paramsStr  = implode("&", $paramsArr);
        $requestUrl = $this->moduleBaseUrl . "?" . $paramsStr;

        return file_get_contents($requestUrl);
    }

    /**
      * Extrahieren eines JSON-Strings aus einem HTML-Grundgerüst
      *
      * Diese Funktion wurde eingeführt, da HOPS Modul-Details
      *     als HTML-Dokument mit HTML-Grundgerüst zurückgibt.
      * Der JSON-String liegt als Inhalt des body-Elements vor.
      *
      * @param string $mixedContent HTML-Grundgerüst mit eingebettetem JSON
      *
      * @return string JSON-String
      */
    private function extractJSONStringFromHTMLBody($mixedContent) {
        
        $matches = array();

        if(     preg_match("/<body>\\s*(.*)\\s*<\\/body>/",
                           $mixedContent,
                           $matches)
            &&  count($matches) === 2) {

            return $matches[1];
        }

        return $mixedContent;
    }

    /**
      * In Freitext genutzte style-Attribute, br-Elemente
      *     und geschützte Leerzeichen entfernen
      *
      * @param string $JSONString Unsauberer JSON-String
      *
      * @return string gesäuberter JSON-String
      */
    private function sanitizeJSONString($JSONString) {
        
        $JSONString = preg_replace("/(\\s*style=\\\\\".*?\\\\\")|(&nbsp;)/", "", $JSONString);
        $JSONString = preg_replace("/(<br.*?>)/", "\n", $JSONString);

        return $JSONString;
    }

    /**
      * Liste von mit Kommata getrennten Dozenten auftrennen
      *     und Dozenten-Kennung extrahieren
      *
      * @param object $module Modul-Objekt (aus JSON per json_decode)
      *
      * @return object Modul-Objekt mit besserer Datenstruktur
      *                 für Zugriff auf Dozenten-Infos
      */
    private function parseModuleDozenten($module) {

        $dozenten = explode(',', $module->DOZENTEN);

        $dozentenObjArr = array();

        foreach($dozenten as $dozent) {
            $dozentenObj = new stdClass();

            $dozentenObj->KUERZEL = NULL;
            $dozentenObj->NAME   = $dozent;

            $matches = array();

            if(    preg_match("/^(.*)\\s\\((.*)\\)/", $dozent, $matches)
                && count($matches) === 3 ) {

                $dozentenObj->KUERZEL = $matches[2];
                $dozentenObj->NAME    = $matches[1];
            }

            $dozentenObjArr[] = $dozentenObj;
        }

        $module->DOZENTEN = $dozentenObjArr;

        return $module;
    }

    /**
      * Als String vorliegende, numerische und boolesche Werte
      *     anhand einer Feldname-zu-Typ-Map konvertieren
      *
      * @param object $module Modul-Objekt (aus JSON per json_decode)
      *
      * @return object Modul-Objekt mit richtig konvertierten Werten
      */
    private function parseModuleValues($module) {

        foreach($this->moduleValueFieldnames as $fieldname => $valType) {
            if(!isset($module->$fieldname))
                continue;

            $val = $module->$fieldname;

            switch($valType) {
                case 'num':
                    if(is_numeric($val))
                        $val *= 1;
                    break;

                case 'bool':
                    $val = is_numeric($val) ? boolval($val): null;
                    break;
            }

            $module->$fieldname = $val;
        }

        return $module;
    }

    /**
      * Parsen von nicht atomaren Wert, der Angaben bezüglich
      *     Studiengänge enthält und Semester, zu denen das Modul
      *     über die jeweiligen Studiengänge belegt werden / werden können
      *
      * @param object $module Modul-Objekt (aus JSON per json_decode)
      *
      * @return object Modul-Objekt mit besserer Datenstruktur
      *                 für Zugriff auf Studiengang- und Semester-Infos
      */
    private function parseModuleCourseAndSemester($module) {
    
        $coursesAndSemesters = $module->SG_SE;
        $coursesAndSemesters = explode(',', $coursesAndSemesters);
        $coursesAndSemestersObj = new stdClass();
        
        foreach($coursesAndSemesters as $coursesAndSemestersItem) {

            $matches = array();
            
            if(    preg_match("/(\\w+)\\s(\\d+)\\s(\\w+)/",
                              $coursesAndSemestersItem,
                              $matches)
                && count($matches) === 4 ) {

                $course     = $matches[1];
                $semester   = intval($matches[2]);
                $program    = $matches[3];

                if(!isset($coursesAndSemestersObj->$program)) {
                    $coursesAndSemestersObj->$program              = new stdClass();
                    $coursesAndSemestersObj->$program->SEMESTER    = $semester;
                    $coursesAndSemestersObj->$program->STUDIENGANG = $course;
                }
                else {
                    $coursesAndSemestersObj->$program->SEMESTER =
                        (array)$coursesAndSemestersObj->$program->SEMESTER;

                    $coursesAndSemestersObj->$program->SEMESTER[] = $semester;
                }
            }
        }

        $module->SG_SE = $coursesAndSemestersObj;

        return $module;
    }
}
