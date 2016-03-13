<?php

$classDir = dirname(__FILE__) . '/../..';

require $classDir . '/hops_modules.php';

$nameTmpFile = $classDir . '/modules_dump.tmp.json';

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
 * Module nach Pflichtfach filtern
 */

$filterFunc = function($moduleObj) {
    $isPF = $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH;
    
    return $isPF;
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

$semesterArr = $hopsModules->filterModulesBy($filterFunc)->getModulesAsBucketsBy($selectorFunc);
unset($semesterArr['unknown']); // Module entfernen, die keinem Semester des MI-Studiengangs zugeordnet war

$semesters = array_keys($semesterArr);
sort($semesters);

?>

<div>

<?php foreach($semesters as $semester): ?>
    <div class="semester">    

        <div class="square simple">
            <h2><strong>Studiensemester <?= $semester ?></strong></h2>
        </div>

        <?php foreach($semesterArr[$semester] as $module): ?>

            <div class="square module">
                <h2> <?= $module->BEZEICHNUNG; ?> </h2>

                <?php $dozentenArr = array(); ?>

                <?php foreach($module->DOZENTEN as $dozent): ?>

                    <?php $dozentenArr[] = '<a href="#">' . $dozent->NAME . '</a>'; ?>

                <?php endforeach; ?>
                
                <?= implode(', ', $dozentenArr) ?>

                <div class="module_infos">
                    <P class="moduletype"><?= ($module->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH) ? "Pflichtfach": "" ?></p>
                    <p class="ectsp"><?= $module->MODULCREDITS ?> ECTS-P</p>
                    <p class="sws"><?= $module->MODULSWS ?> SWS</p>
                    <?php
                        $moduleSemesters = $module->SG_SE->MI_B->SEMESTER;
                        echo ((is_array($moduleSemesters) && count($moduleSemesters) > 1) ? 'halbjährlich': 'jährlich') . ' angeboten';
                    ?>
                </div>
                
                <a class="details" href="details.php?mid=<?= $module->MODUL_ID ?>">>></a>
                
            </div>

        <?php endforeach; ?>
    
    </div>

<?php endforeach; ?>

</div>
