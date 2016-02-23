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
     * Fragt die Moduldaten beim HOPS ab und restrukturiert sie bis zu einem Gewissen grad
     */
    $hopsModules = new HOPSModules();
    
    /* Für Medieninformatik Master */
    // $hopsModules = new HOPSModules(array(HOPSModules::PROGRAM => HOPSModules::PROGRAM_MI_M));
    
    $hopsModules->toJSONFile($nameTmpFile);
}


/*
 * Module nach Eigenschaften filtern
 */
function filterModules($hopsModules) {

    $func = function($moduleObj) {
        $isPF      = $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH;
        $isSeminar = $moduleObj->FACH_TYP    === HOPSModules::FT_SEMINAR;
        
        return $isPF && $isSeminar;
    };

    return $hopsModules->filterModulesBy($func);
}



                         /*     alle Module         :      gefilterte Module    */ 
$modulesArr = ( true ) ? $hopsModules->getModules() : filterModules($hopsModules);




$modulesDataJSON = json_encode($modulesArr, JSON_PRETTY_PRINT);

header('Content-Type', 'application/json');
header('Content-Length', count($modulesDataJSON));

echo $modulesDataJSON;
