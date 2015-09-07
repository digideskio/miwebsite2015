<!DOCTYPE html>
<html lang="<?php echo $site->language()->code(); ?>">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title><?php echo $site->title()->html() ?> | <?php echo $page->title()->html() ?></title>
<meta name="description" content="<?php echo $site->description()->html() ?>">
<meta name="keywords" content="<?php echo $site->keywords()->html() ?>">

	
<style>
<?php
require_once "assets/lib/scssphp/scss.inc.php";

$scss_filepath = "assets/css/scss/above-the-fold.scss";
$css_cache_filepath = "assets/css/cached/above-the-fold.css";

try{
    $scss = new Leafo\ScssPhp\Compiler();
    $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
    
    if(     file_exists($css_cache_filepath)
        &&  filemtime($css_cache_filepath) > filemtime($scss_filepath)) {
        $css = file_get_contents($css_cache_filepath);
    }
    else {
        $css = $scss->compile("@import '". $scss_filepath ."'");
        file_put_contents($css_cache_filepath, $css);        
    }

    echo $css;
}catch(Exception $e){
    echo $e->getMessage();
}
?>
</style>


<script>
	
// Stack of js actions, that have to be fired, after the page is loaded
var init_actions = new Array();	

</script>
</head>

<body>


<header class="container">
	
	<div class="row">
		<div class="col-md-12">
			<div class="logo">
				<a href="<?php echo $site->homePage()->url(); ?>">
			<?php
				atomicdesign::output("molecule", "heading", array("content" => $site ));
			?>
				</a>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="main-menu">
			<?php
				$items = structhelper::make_menu_items($pages);
				atomicdesign::output("molecule", "menu", array("items" => $items ));
			?>
			</div>
		</div>
	</div>
	
</header>
