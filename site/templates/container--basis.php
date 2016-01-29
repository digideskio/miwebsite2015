<?php
	//if(preg_match("=(1|true|TRUE)=",$page->hide_in_lang())){  go("/" + $site->language()->code()); }
	snippet('header', array("content" => $page));
?>

<!-- Content Block -->
<?php

atomicdesign::output("organism","container--rows"); ?>

<!-- EO-Content Block -->
<?php snippet( c::get('customs-folder') . 'footer'); ?>
