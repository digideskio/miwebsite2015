<!-- Molecule: default -->
<?php 

if($content->headline() != ""){
	atomicdesign::output("atom", "headline", array("text" => $content->headline()));
}

if($content->subheadline() != ""){
	atomicdesign::output("atom", "subheadline", array("text" => $content->subheadline()));
}

if($content->text() != ""){
	atomicdesign::output("atom", "text", array("text" => $content->text()));
} 

?>


