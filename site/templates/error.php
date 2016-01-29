<?php
	
	$def_lang = $site->defaultLanguage()->code(); 
	
	// gibt es einen Shortcut zu einer anderen Seite?
	$meta = $site->find("uebergreifende-informationen")->content($def_lang);
	if($meta->shortcuts()->exists() && $meta->shortcuts()->toStructure()->count() > 0){
	
		// URL Segment ziehen
		$seg = preg_replace("=^/=", "", $_SERVER['REQUEST_URI']);

		foreach($meta->shortcuts()->yaml() as $shortcut){
			if($shortcut["shortcut"] == $seg && strlen($shortcut["target"]) > 5){
				
				// Wurde eine korrekte URL als Ziel eigegeben?
				$sn = $_SERVER['SERVER_NAME'];
				$target = (preg_match("=$sn=", $shortcut["target"])) ? $shortcut["target"] : $sn ."/". $site->language() . $shortcut["target"];
				
				// Gibts ein Protokoll?
				$target = (preg_match("=^http://=", $target)) ? $target : "http://". $target;
				
				// Weiterleiten
				header("Location: $target"); exit;
			}
		}
	}

?>
<?php snippet('header') ?>

<section class="content container">
	<!--div class="row">
		<div class="col-md-12 bild">
			<figure>
				<?php if($page->hasImages()): $pic = $page->images()->first(); ?>
		 		<img class="scale hidden-portrait" src="/assets/php/timthumb/images.php?src=<?php echo $pic->url(); ?>&w=1200&h=400&q=80">
		 		<img class="scale visible-portrait-inline" src="/assets/php/timthumb/images.php?src=<?php echo $pic->url(); ?>&w=1200&h=1200&q=80">
		 		<?php endif; ?>
		 	</figure>
		 </div>
	</div-->
	
	<div class="row  padding-top-2">
    	<div class="col-md-4 text"><h1><?php echo html($page->title()) ?></h1></div>
		<div class="col-md-8 text"><?php echo kirbytext($page->text()) ?></div>
	</div>

</section>
<?php snippet('sidebar') ?>
<?php snippet('footer') ?>
