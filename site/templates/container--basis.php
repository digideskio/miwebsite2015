<?php
	include_once("assets/php/functions.php");
	snippet('header', array("content" => $page));
?>

<!-- Content Block -->
<?php
atomicdesign::output("organism","container--rows"); ?>

<!-- EO-Content Block -->
<?php snippet('footer'); ?>
