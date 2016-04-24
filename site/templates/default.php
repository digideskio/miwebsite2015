
<?php
	//if(preg_match("=(1|true|TRUE)=",$page->hide_in_lang())){  go("/" + $site->language()->code()); }
	snippet('header', array("content" => $page, "mode" => "singleContent"));
?>

<!-- Content Block -->
<section id="<?= $page->uid() ?>" class="content container">
<?php

			// Snip holen
			$template = atomicdesign::get_snip( $page->uid(), "default");
      $template = atomicdesign::get_snip( $page->intendedTemplate(), $template);

      snippet($template, array(
				'content' 			=> $page,
				'snippet' 			=> $template,
				'mode'          => "singleContent"
			));
?>
</section>

<!-- EO-Content Block -->
<?php
  snippet( c::get('customs-folder') . 'footer');
?>
