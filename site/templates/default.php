<?php
  include_once("assets/php/functions.php");
	snippet('header', array("content" => $page));
?>

<!-- Content Block -->
<?php

$ebene = $page->children()->visible();
if($ebene->count() === 0){
  $ebene = $page->parent()->children()->visible();
}
atomicdesign::output("organism","container--rows", array("use_containers" => $ebene)); ?>

<!-- EO-Content Block -->
<?php snippet('footer'); ?>
