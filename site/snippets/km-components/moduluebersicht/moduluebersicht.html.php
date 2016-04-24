<?php

$classDir = dirname(__FILE__) . '/../../../..';


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

<div class="col-md-12 moduluebersicht">

    <div class="modul-filtergroups">
      <div class="modul-filter">
        <button class="btn btn-primary">1</button>
        <button class="btn btn-primary">2</button>
        <button class="btn btn-primary">3</button>
        <button class="btn btn-primary">4</button>
        <button class="btn btn-primary">5</button>
        <button class="btn btn-primary">6</button>
      </div>
      <div class="modul-filter pull-right">
        <button class="btn btn-secondary">A-Z</button>
      </div>
    </div>

  <?php foreach($semesters as $semester): ?>
  <!--div class="semester semester-<?=$semester;?>"-->

    <?php foreach($semesterArr[$semester] as $module):
      $dozentenArr = array();


      //var_dump($module); exit;

      foreach($module->DOZENTEN as $dozent){
        $dozentenArr[] = '<a href="#">' . $dozent->NAME . '</a>';
      }

      $typ = ($module->PFLICHTFACH === HOPSModules::PF_PFLICHTFACH) ? "Pflichtfach": "";
      $moduleSemesters = $module->SG_SE->MI_B->SEMESTER;
    ?>

    <article class="modul modul-<?=$module->KURZBEZ;?>" data-props="<?=$module->KURZBEZ;?><?=$typ;?> sem-<?=$semester;?><">
      <header class="head collapsed" data-toggle="collapse" data-target="#<?=$module->KURZBEZ;?>-content">
        <h1 class="headline name"><?= $module->BEZEICHNUNG; ?> <i class="indicator pull-right fa fa-angle-up" aria-hidden="true"></i></h1>

      </header>

      <div class="modulinfos collapse" id="<?=$module->KURZBEZ;?>-content">
        <dl class="modul-daten">
          <?php if(sizeof($typ) > 0): ?>
            <dt>Typ:</dt><dd><?= $typ ?></dd>
          <?php endif; ?>

          <dt>Creditpoints:</dt><dd><?= $module->MODULCREDITS ?> ECTS-P</dd>
          <dt>Semesterwochenstunden:</dt><dd><?= $module->MODULSWS ?></dd>
          <dt>Studiensemester:</dt><dd><?=$semester;?></dd>
          <dt>Angebot:</dt><dd><?php echo ((is_array($moduleSemesters) && count($moduleSemesters) > 1) ? 'halbjährlich': 'jährlich') . ' angeboten'; ?></dd>
          <?php
            $key = (sizeof($dozentenArr) > 1) ? "Dozenten: " : "Dozent: ";
          ?>
          <dt><?=$key;?></dt><dd><?= implode(', ', $dozentenArr) ?></dd>
        </dl>

        <?php if(sizeof($module->LEISTUNGEN) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Leistungen:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->LEISTUNGEN); ?></div>
        </div>
        <?php endif; ?>

        <?php if(sizeof($module->MODULLEHRFORM) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Lehrform:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->MODULLEHRFORM); ?></div>
        </div>
        <?php endif; ?>

        <?php if(sizeof($module->MODULVORAUSSETZUNG) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Voraussetzungen:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->MODULVORAUSSETZUNG); ?></div>
        </div>
        <?php endif; ?>

        <?php if(sizeof($module->MODULLERNZIELE) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Ziele:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->MODULLERNZIELE); ?></div>
        </div>
        <?php endif; ?>

        <?php if(sizeof($module->MODULINHALT) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Inhalt:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->MODULINHALT); ?></div>
        </div>
        <?php endif; ?>

        <?php if(sizeof($module->MODULLITERATUR) > 0):?>
        <div class="modul-info">
          <h2 class="modul-info--title">Literatur:</h2>
          <div class="modul-info--text"><?php echo kirbytext($module->MODULLITERATUR); ?></div>
        </div>
        <?php endif; ?>
      </div>

    </article>
    <?php endforeach; ?>
  <!--/div-->

<?php endforeach; ?>

</div>



