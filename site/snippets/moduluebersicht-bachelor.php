<?php

$classDir = dirname(__FILE__) . '/../..';
require $classDir . '/assets/lib/hops/hops_modules.php';

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
    $isPF = (isset($moduleObj->PFLICHTFACH) && $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH) ? $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH : false;
//    $isPF = $moduleObj->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH;
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

<div class="col-md-12 grid js-isotope">
<button onclick="$grid.isotope({ sortBy : 'random' });">Random</button>
  <?php foreach($semesters as $semester): ?>
  <!--div class="semester semester-<?=$semester;?>"-->

    <?php foreach($semesterArr[$semester] as $module):
      $dozentenArr = array();

      foreach($module->DOZENTEN as $dozent){
        $dozentenArr[] = '<a href="#">' . $dozent->NAME . '</a>';
      }
      //var_dump($module);
    ?>

    <article class="modul modul-<?=$module->KURZBEZ;?>" id="">
      <p class="modname"><?= $module->BEZEICHNUNG; ?></p>
      <header class="head" data-toggle="collapse" data-target="#<?=$module->KURZBEZ;?>-content">
        <h1 class="headline name"><?= $module->BEZEICHNUNG; ?></h1>
      </header>

      <div class="modulinfos collapse" id="<?=$module->KURZBEZ;?>-content">
        <p class="modultyp"><?= ($module->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH) ? "Pflichtfach": "" ?></p>
        <p class="ectsp"><?= $module->MODULCREDITS ?> ECTS-P</p>
        <p class="sws"><?= $module->MODULSWS ?> SWS</p>
        <p class="studiensemester"><?=$semester;?></p>
        <?= implode(', ', $dozentenArr) ?>
        <?php
        $moduleSemesters = $module->SG_SE->MI_B->SEMESTER;
        echo ((is_array($moduleSemesters) && count($moduleSemesters) > 1) ? 'halbjährlich': 'jährlich') . ' angeboten';
        ?>
      </div>

    </article>
    <?php endforeach; ?>
  <!--/div-->

<?php endforeach; ?>

</div>



