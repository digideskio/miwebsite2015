<?php


class HOPSModules {

    const PROGRAM      = 'program';
    const PROGRAM_MI_B = 'MI_B';
    const PROGRAM_MI_M = 'MI_M';

    const EMPHASIS = 'emphasis';

    const PF_PFLICHTFACH     = 'PF';
    const PF_WAHLPFLICHTFACH = 'WPF';

    const FT_VORLESUNG = 'V';
    const FT_SEMINAR   = 'S';


    private $moduleBaseUrl = "http://www.medieninformatik.th-koeln.de/dev/api-bridge.php";
    private $params = array(
        self::PROGRAM  => self::PROGRAM_MI_B,
        self::EMPHASIS => NULL
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

    private $filterActive = false;

    private $modules           = array();
    private $moduleIDs         = array();
    private $lecturerModuleMap = array();

    private $modulesBackup = array();


    function __construct($filterParams = array(), $fetchData = true) {
        if(!$fetchData)
            return;

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

            $moduleParts = json_decode($moduleDetailsJSONString);

            if(    is_array($moduleParts)
                && count($moduleParts) > 0 ) {
                $moduleParts = $this->handleModuleStruct($moduleParts);
            }
            else
                continue;

            foreach($moduleParts as $modulePart) {
                $currMID = $modulePart->MODUL_ID;
                $this->moduleIDs[$currMID] = $modulePart->BEZEICHNUNG;

                /* Da alle Werte vom Typ String und nicht immer atomar sind,
                    werden sie aufgetrennt und in ihren eigentlichen Typ konvertiert */
                $modulePart = $this->parseModuleDozenten($modulePart);
                $modulePart = $this->parseModuleValues($modulePart);
                $modulePart = $this->parseModuleCourseAndSemester($modulePart);

                $this->modules[$currMID] = $modulePart;
            }
        }

        /* Temporäres Array mit Dozenten-Objekten und ihnen zugewiesenen Modulen */
        $this->createLecturerModuleMap();
    }


    /**
     * Rückgabe aller Module als Array
     *
     * @return array Array mit Modul-Objekten
     */
    public function getModules() {
        return $this->modules;
    }


    /**
     * Rückgabe aller Module-IDs samt Modulbezeichnung als assoziatives Array
     *
     * @return array Array mit Modul-IDs
     */
    public function getModuleIDs() {
        return $this->moduleIDs;
    }


    /**
     * Einordnung von Modulen in Buckets (Gruppierung), anahnd einer Eigenschaft,
     * die per Selektorfunktion selektiert wird
     *
     * @param function $func Selektorfunktion, die die Eigenschaft eines Moduls selektiert, wonach Gruppen erzeugt werden
     *
     * @return array Array mit gruppierten Modul-Objekten
     */
    public function getModulesAsBucketsBy($func = FALSE) {
        if(!is_callable($func))
            throw new Exception("Keine Selektorfunktion mitgegeben!");

        $moduleBuckets = array();

        foreach($this->getModules() as $moduleID => $moduleData) {
            $bucket_var = $func($moduleData);

            if(!isset($moduleBuckets))
                $moduleBuckets[$bucket_var] = array();

            $moduleBuckets[$bucket_var][] = $moduleData;
        }

        return $moduleBuckets;
    }


    /**
     * Rückgabe einer Liste von Dozenten und ihren Modulen
     *
     * @return array Array mit Dozenten und ihren Modulen
     */
    public function getLecturerModuleMap() {
        return $this->lecturerModuleMap;
    }


    /**
     * Filterung von Modulen mittels Filterfunktion
     *
     * @param function $func Filterfunktion, die mit jedem Modul-Objekt aufgerufen wird
     *
     * @return array Array mit gefilterten Modul-Objekten
     */
    public function filterModulesBy($func = FALSE) {
        if(!is_callable($func))
            throw new Exception("Keine Filterfunktion mitgegeben!");

        if(!$this->filterActive) {
            $this->modulesBackup = $this->modules;
        }

        $filteredModules = array();

        foreach($this->getModules() as $moduleID => $moduleData) {
            if($func($moduleData))
                $filteredModules[$moduleID] = $moduleData;
        }

        $this->modules = $filteredModules;
        $this->recreateMaps();

        $this->filterActive = true;

        return $this;
    }


    /**
     * Alle Filteraktionen zurücksetzen
     *
     * @return object HOPSModules-Instanz
     */
    public function removeFilter() {
        if($this->filterActive) {
            $this->filterActive = false;

            $this->modules = $this->modulesBackup;
            $this->recreateMaps();
        }

        return $this;
    }


    /**
     * Angabe bezüglich darüber, ob mit gefilterten Modulen gearbeitet wird
     *
     * @return bool Status darüber, ob Filter angewandt wurden
     */
    public function filterActive() {
        return $this->filterActive;
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
     * Besonderheiten von bestimmten Modulen handhaben
     *
     * @param array $moduleParts Module und ihre Variationen
                                 (semesterübergreifende oder mehrdozentrige Module)
     *
     * @return array
     */
    private function handleModuleStruct($moduleParts) {

        $resolvedModuleParts = array();

        $mID = $moduleParts[0]->MODUL_ID;

        switch($mID) {
            case '1543': // Theoretische Informatik I / II
                $i = 1;
                foreach($moduleParts as $modulePart) {
                    $modulePart->MODUL_ID .= '_' . $i;
                    $i++;
                    $modulePart->MODULCREDITS = '' . (intval($modulePart->MODULCREDITS) / 2);
                    $modulePart->MODULSWS   = '' . (intval($modulePart->MODULSWS) / 2);
                    $resolvedModuleParts[] = $modulePart;
                }
                break;

            case '1295': // Projektmanagment
                $firstModuleVariant = $moduleParts[0];
                $firstModuleVariant->BEZEICHNUNG = $firstModuleVariant->MODULBEZEICHNUNG;

                for($i = 1; $i < count($moduleParts); $i++) {
                    $currModuleVariant = $moduleParts[$i];

                    foreach($currModuleVariant as $prop => $val) {
                        if(     $prop === "DOZENTEN"
                            &&  !is_null($val)
                            &&  strpos($firstModuleVariant->DOZENTEN, $val) === FALSE) {

                            $firstModuleVariant->DOZENTEN .= (',' . $val);
                        }
                        else if($prop === "SG_SE") {
                            if(!is_string($firstModuleVariant->SG_SE))
                                $firstModuleVariant->SG_SE = $val;
                            else
                            $firstModuleVariant->SG_SE .= ',' . $val;
                        }

                        if(     is_null($firstModuleVariant->$prop)
                            &&  !is_null($currModuleVariant->$prop) ) {
                            $firstModuleVariant->$prop = $currModuleVariant->$prop;
                        }
                    }
                }

                $resolvedModuleParts[] = $firstModuleVariant;

                break;

            case '1538': // Medientechnik und Produktion
            case '1540': // Audiovisuelles Medienprojekt
                $modulePart1 = $moduleParts[0];
                $modulePart1->MODULCREDITS = '' . (intval($modulePart1->MODULCREDITS) / 2);
                $modulePart1->BEZEICHNUNG = $modulePart1->MODULBEZEICHNUNG;

                $modulePart2 = new stdclass();

                foreach($modulePart1 as $prop => $val) {
                    $modulePart2->$prop = $val;
                }

                $sgSe = $modulePart1->SG_SE;

                $sgSe = preg_replace_callback('/ [1-9] /',
                                              function($val) {
                                                  return ' ' . (intval($val[0]) + 1) . ' ';
                                              },
                                              $sgSe);

                $modulePart2->SG_SE = $sgSe;

                $resolvedModuleParts[] = $modulePart1;
                $resolvedModuleParts[] = $modulePart2;

                $i = 1;
                foreach($resolvedModuleParts as &$module) {
                    $module->BEZEICHNUNG .= ' ' . $i;
                    $module->MODUL_ID .= '_' . $i;
                    $i++;
                }

                break;

            default:
                $resolvedModuleParts = $moduleParts;
                break;
        }


        return $resolvedModuleParts;
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


    /**
     * Erzeugung der Dozent-zu-Modul-Map
     *
     */
    private function createLecturerModuleMap() {

        $this->lecturerModuleMap = array();

        foreach($this->getModules() as $moduleID => $moduleData) {
            foreach($moduleData->DOZENTEN as $dozent) {

                if(!isset($this->lecturerModuleMap[$dozent->KUERZEL])) {
                    $dozentObj = new stdClass();
                    $dozentObj->NAME = $dozent->NAME;
                    $dozentObj->MODULE_IDS = array();

                    $this->lecturerModuleMap[$dozent->KUERZEL] = $dozentObj;
                }

                $this->lecturerModuleMap[$dozent->KUERZEL]->MODULE_IDS[] = $moduleID;
            }
        }
    }


    /**
     * Rekonstruiert die ModulID-zu-Modulbezeichnung-Map
     *  und Dozent-zu-Module-Map, ohne über den Konstruktor zu gehen.
     * Wird bebötigt, wenn die Moduldaten aus einer Datei eingelesen werden.
     *
     */
    private function recreateMaps() {

        $this->moduleIDs = array();

        foreach($this->modules as $moduleID => $moduleData) {
            $this->moduleIDs[$moduleID] = $moduleData->BEZEICHNUNG;
        }

        $this->createLecturerModuleMap();
    }


    /**
     * Serialisiert die Moduldaten als JSON-Datei
     *
     */
    public function toJSONFile($filename) {
        return file_put_contents($filename, json_encode($this->getModules(), JSON_PRETTY_PRINT));
    }


    /**
     * Deserialisieren einer JSON-Datei, in der die Moduldaten vorliegen
     *
     * @param string $filename Pfad zur Datei, die deserialisiert werden soll
     */
    public static function fromJSONFile($filename) {
        if(!file_exists($filename))
            throw new Exception("Datei nicht gefunden: " . $filename);

        $fileContent = file_get_contents($filename);

        $hopsModules = new self(array(), false);
        $hopsModules->modules = json_decode($fileContent);
        $hopsModules->recreateMaps();

        return $hopsModules;
    }
}
