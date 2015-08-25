<?php atomicdesign::output("organism", "header"); ?>

<!-- Template: container--rows -->
<main class="main" role="main">

	<section class="container">
		<div class="row">
			<div class="col-md-12">
			<?php 
				atomicdesign::output("organism", "content--article", array("content" => $page, "class" => $page->layout()));
			?>
			</div>
		</div>

	</section>
</main>

<?php atomicdesign::output("organism", "footer"); ?>
