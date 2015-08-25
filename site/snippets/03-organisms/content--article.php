<!-- Organism: Article -->
<?php if( isset($icon) ): ?>

<div class="<?=$class?> icon" style="background-image: url(<?=$icon?>)">

<?php else: ?>

<div class="<?=$class?>">

<?php endif; ?>

<?php 
	
	if(!isset($docs)){ $docs = false; }
	
	atomicdesign::output("molecule", "article", array(
		"content" 	=> $content,
		"docs"		=> $docs
	)); 
?>
</div>
