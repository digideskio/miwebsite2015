<?php

kirbytext::$tags['illustration'] = array(
	'attr' => array(
    	'size'
	),
	'html' => function($tag) {
		$config  = c::get("illustrations");

  	$illu    = $tag->attr("illustration");
		$size    = $tag->attr("size", "s");
		$class    = "illustration ". $tag->attr("class", "") . " size-" . $size;
    $baseurl = $config["base-url"];

    $url = kirby()->urls()->index() . $baseurl."/".$illu.".svg";
    $svg = file_get_contents($url);

    // XML Header killen und ID anpassen
    $svg = preg_replace("=<\?.*?>=", "", $svg);
    $svg = preg_replace("=id\=\".*?\"=", "id=\"".$illu."\"  class=\"".$class."\"", $svg);

		//$html = "<img src=\"".$baseurl."/".$illu.".svg\">";
    echo $svg;
		return "";
	}
);
?>
