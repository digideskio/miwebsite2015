<!-- Organism: Rows -->

<?php 
	$heading_content = isset($content) ? $content: $page;
?>

<?php if($heading_content->headline_zeigen() == "true"): ?>
<div class="row">
	<div class="col-md-8">
		<?php
			atomicdesign::output("molecule", "heading", array("content" => $heading_content, "class" => "h--hero" ));
		?>
	</div>
</div>
<?php endif; ?>

<?php 
	//$containers = get_container($site, $pages, $page); 
	if(isset($content)){
		$containers = $content->children()->visible();
	}else{
		$containers = $page->children()->visible();
	}
	//$containers = $content->children()->visible(); //get_container($site, $pages, $page); 
	foreach($containers as $container): 
?>
<div class="row">
	<div class="col-md-12">
	<?php
	
	// Bilder holen
	$bilder = structhelper::get_images_from_article( $container );
	
	// Dokumente holen
	$docs = structhelper::get_documents_from_article( $container );	

	atomicdesign::resolve_and_output($container, array(
		'content' 	=> $container, 
		'class' 	=> $container->layout(),
		'bilder' 	=> $bilder,
		'docs'		=> $docs
	)); 
	?>
	</div>
</div>
<?php endforeach; ?>
