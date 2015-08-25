<!-- Organism: Slideshow -->
<div class="<?=$class?>">

<div class="head">
	<?php 
		atomicdesign::output("molecule", "heading"), array(
			"content" 	=> $content
		));
	?>
</div>

<?php 
	atomicdesign::output("molecule", "slideshow"), array(
		"content" 	=> $content, 
		"bilder" 	=> $bilder,
		"kennung"	=> $content->slug(),
		"autostart"	=> $content->autostart()
	));
?>
</div>
