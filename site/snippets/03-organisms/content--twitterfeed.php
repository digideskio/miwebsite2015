<!-- Organism: Twitterfeed -->

<?php // Shall we show a ruler to Seperate the Content?
	if($content->ruler() == "ruler"){
		atomicdesign::output("atom", "hr");
	}
?>

<?php 
	$icon = false;
	if( isset($bilder["icon"]) ){ $icon = $bilder["icon"]; }
	
	atomicdesign::output("organism", "content--article", array("content" => $content, "class" => $class, "icon" => $icon ));
?>

<div id="twitterfeed" class="row"></div>

<script type="text/javascript">
	init_actions.push("twitter.func.init();");
</script>
