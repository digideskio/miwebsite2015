<?php echo $page->intendedTemplate(); ?>

<?php snippet('header') ?>

<section class="content container">
	<div class="row">
		<div class="col-md-12 bild">
			<figure>
				<?php if($page->hasImages()): $pic = $page->images()->first(); ?>
		 		<img class="scale hidden-portrait" src="/assets/php/timthumb/images.php?src=<?php echo $pic->url(); ?>&w=1200&h=400&q=80">
		 		<img class="scale visible-portrait-inline" src="/assets/php/timthumb/images.php?src=<?php echo $pic->url(); ?>&w=1200&h=1200&q=80">
		 		<?php endif; ?>
		 	</figure>
		 </div>
	</div>

	<div class="row">
    	<div class="col-md-4 text"><h1><?php echo html($page->title()) ?></h1></div>
		<div class="col-md-8 text"><?php echo kirbytext($page->text()) ?></div>
	</div>

</section>
<?php snippet('sidebar') ?>
<?php snippet('footer') ?>

