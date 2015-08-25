<!-- Organism: Blogentry -->
<div class="<?=$class?>">
<?php 
	if(!isset($docs)){ $docs = false; }
	
	atomicdesign::output("molecule", "article", array(
		"content" 	=> $content,
		"docs"		=> $docs
	));
?>
</div>

