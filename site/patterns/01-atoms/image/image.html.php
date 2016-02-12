<?php
	if(!isset($class)){ $class = ""; }
	$portrait = (isset($portrait)) ? $portrait : "";
?>

<?php if(preg_match("=\.svg=", $bild)): ?>

	<img class="scale <?php echo $class?>" src="<?php echo $bild; ?>">

<?php else: ?>

	<?php if($portrait == "true"): ?><div class="portrait"><?php endif; ?>

		<img class="scale <?php echo $class?>" sizes="(min-width: 40em) 80vw, 100vw" data-srcset="/assets/php/timthumb/images.php?src=<?php echo $bild; ?>w=375&q=85 375w, /assets/php/timthumb/images.php?src=<?php echo $bild; ?>w=480&q=85 480w, /assets/php/timthumb/images.php?src=<?php echo $bild; ?>w=768&q=85 768w" alt="<?= $alttext; ?>">

	<?php if($portrait == "true"): ?></div><?php endif; ?>

<?php endif; ?>
