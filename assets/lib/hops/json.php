<?php

require 'hops_modules.php';

$nameTmpFile = 'modules_dump.tmp.json';

try{
    /* 
     * Einlesen der temporären Datei ermöglicht den Aufruf von diversen
     *    hilfreichen Methoden auf Basis der Moduldaten; z.B. 'getLecturerModuleMap'.
     *  Ansonsten kann der Inhalt der temporären Datei auch direkt ausgegeben werden,
     *    ohne den Umweg über HOPSModules zu gehen.
     */
    $hopsModules = HOPSModules::fromJSONFile($nameTmpFile);
}
catch(Exception $e) {
    /*
     * Fragt die Moduldaten beim HOPS ab und restrukturiert sie bis zu einem Gewissen Grad
     */
    $hopsModules = new HOPSModules();
    
    /* Für Medieninformatik Master */
    // $hopsModules = new HOPSModules(array(HOPSModules::PROGRAM => HOPSModules::PROGRAM_MI_M));
    
    $hopsModules->toJSONFile($nameTmpFile);
}


/*
 * Module nach Eigenschaften filtern
 */

$filterFunc = function($moduleObj) {
    $isPF      = $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH;
    // $isSeminar = $moduleObj->FACH_TYP    === HOPSModules::FT_SEMINAR;
    
    return $isPF; // && $isSeminar;
};




/*
 * Module nach Semester gruppieren (Semester => Bucket)
 */
$selectorFunc = function($moduleObj) {
    
    if( !isset($moduleObj->SG_SE->MI_B) || 
        !isset($moduleObj->SG_SE->MI_B->SEMESTER) )
        return 'unknown';
    
    $semester = $moduleObj->SG_SE->MI_B->SEMESTER;
    
    if(is_array($semester) && !empty($semester))
        $semester = $semester[0]; // Erstes Semester ist das Semester, an dem die Veranstaltung bzw. das Modul regulär stattfindet
    
    return $semester;
};


$modulesArr = array();

switch( '' ) {
    case 'filter': /* gefilterte Module */
        $modulesArr = $hopsModules->filterModulesBy($filterFunc)->getModules();
        $hopsModules->removeFilter(); /* Um nicht weiter auf Basis einer Liste mit gefilterten Modulen zu arbeiten */
        break;
        
    case 'bucket': /* gruppierte Module */
        $modulesArr = $hopsModules->getModulesAsBucketsBy($selectorFunc);
        break;
    
    case 'filter_n_bucket': /* gefilterte und gruppierte Module */
        $modulesArr = $hopsModules->filterModulesBy($filterFunc)->getModulesAsBucketsBy($selectorFunc);
        $hopsModules->removeFilter(); /* Um nicht weiter auf Basis einer Liste mit gefilterten Modulen zu arbeiten */
        break;
    
    default: /* alle Module */
        $modulesArr = $hopsModules->getModules();
}


$modulesDataJSON = json_encode($modulesArr, JSON_PRETTY_PRINT);

header('Content-Type', 'application/json');
header('Content-Length', count($modulesDataJSON));

echo $modulesDataJSON;
