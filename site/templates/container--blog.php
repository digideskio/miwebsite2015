<?php $containers = structhelper::get_blog_container($site, $pages, $page, 200); ?>
<?php atomicdesign::output("organism", "header"); ?>

<!-- Template: container--blog -->
<section class="container">
<?php foreach($containers as $container): ?>
	<div class="row">
		<div class="col-md-12">
			<?php
				
			atomicdesign::resolve_and_output($container, array(
				'content' => $container,
				'class' => $container->layout()
			)); 
			?>
		</div>
	</div>

<?php endforeach ?>
</section>

<?php atomicdesign::output("organism", "footer"); ?>
