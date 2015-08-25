<!-- Organism: Article Overview -->
<div class="<?=$class?>">
	
<?php 
	atomicdesign::output("molecule", "heading", array("content" => $content)); 
	
	if($content->text() != ""){
		atomicdesign::output("atom", "text", array("text" => $content->text()));
	}
?>
	
<?php 
	$containers = $content->children();
	$items = structhelper::make_menu_items($containers);
?>

<?php 
	atomicdesign::output("atom", "list-unordered", array("items" => $items, "class" => "link-list")); 
?>
</div>
