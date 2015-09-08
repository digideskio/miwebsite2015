<!-- Organism: Main-Sidebar -->

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

<div class="row">
	<div class="col-md-8 hauptspalte">
		<?php			
			if(isset($content)){
				$containers = $content->find("hauptspalte")->children()->visible();
			}else{
				$containers = $page->find("hauptspalte")->children()->visible();
			}
			
			foreach($containers as $container): ?>
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
		<?php endforeach ?>
	</div>
	
	<div class="col-md-4 sidebar">
		<?php	
			if(isset($content)){
				$containers = $content->find("sidebar")->children()->visible();
			}else{
				$containers = $page->find("sidebar")->children()->visible();
			}
			foreach($containers as $container) {
			    // Bilder holen
			    $bilder = structhelper::get_images_from_article( $container );

			    // Dokumente holen
			    $docs = structhelper::get_documents_from_article( $container );

                atomicdesign::resolve_and_output($container, array(
				    'content' 	=> $container,
				    'class' 	=> $container->layout(),
				    'bilder'	=> $bilder,
				    'docs'		=> $docs
			    ));
		    }
		?>
	</div>
</div>
