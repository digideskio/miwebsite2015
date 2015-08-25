<!-- Molecule: heading -->
<?php 

if(!isset($class)){ $class = ""; }

$headline = ($content->headline() == "") ? $content->title(): $content->headline();
atomicdesign::output("atom", "headline", array("text" => $headline, "class" => $class));

if($content->subheadline() != ""){
	atomicdesign::output("atom", "subheadline", array("text" => $content->subheadline(), "class" => $class));
}
?>
