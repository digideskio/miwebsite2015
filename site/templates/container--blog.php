<?php $containers = structhelper::get_blog_container($site, $pages, $page, 200); ?>
<?php atomicdesign::output("organism", "header"); ?>

<!-- Template: container--blog -->
<section class="container">
<?php foreach($containers as $container): ?>
	<div class="row">
		<div class="col-md-12">
			<?php
				
			// Snip holen
			$template = atomicdesign::get_snip( $container->uid(), "default"); 
			$template = atomicdesign::get_snip( $container->intendedTemplate(), $template);
		
			snippet($template, array(
				'content' => $container, 
				'snippet' => $template,
				'class' => $container->layout()
			)); 
			?>
		</div>
	</div>

<?php endforeach ?>
</section>

<?php atomicdesign::output("organism", "footer"); ?>
