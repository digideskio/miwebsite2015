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


if(!isset($_GET['mid']) && !is_numeric($_GET['mid'])) {
    ?>
        <p class="error">Unbekannte Modul-ID: <?= $_GET['mid'] ?></p>
    <?php

    die();
}


$mid = $_GET['mid'];
$modulesArr = $hopsModules->getModules();

if(!isset($modulesArr->$mid)) {
    ?>
        <p class="error">Moduldetails für Modul-ID nicht gefunden: <?= $_GET['mid'] ?></p>
    <?php

    die();
}


$module = $modulesArr->$mid;

?>

<div>
    <h1><?= $module->BEZEICHNUNG ?></h1>
    <div class="module_infos">
        <P class="moduletype"><?= ($module->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH) ? "Pflichtfach": "" ?></p>
        <p class="ectsp"><?= $module->MODULCREDITS ?> ECTS-P</p>
        <p class="sws"><?= $module->MODULSWS ?> SWS</p>
        <?php
            $moduleSemesters = $module->SG_SE->MI_B->SEMESTER;
            echo ((is_array($moduleSemesters) && count($moduleSemesters) > 1) ? 'halbjährlich': 'jährlich') . ' angeboten';
        ?>
    </div>

    <?php foreach($module as $key => $val): ?>
        <?php if(!is_string($val) || empty($val)) continue; ?>

        <div class="entry">
            <h2 class="title"> <?= $key ?> </h2>
            <div class="text"> <?= $val ?> </div>
            <div class="clearfix"></div>
        </div>

    <?php endforeach; ?>

</div>
