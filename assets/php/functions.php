<?php

/**
 * Holt die Kinder einer Page
 *
 * @author c.noss@klickmeister.de
 * @return Container Objekte
 */


function get_container($site, $pages, $page) {

	// get all articles
	$containers = $page->children()->visible();
	return $containers;

};

function get_blog_container($site, $pages, $page, $limit) {

	$containers = $pages->find("blog")->children()->visible()->flip()->limit($limit)->sortBy('date', 'desc');//->visible();
	return $containers;


};

/**
 * Returns a String in Kirbytext Linksyntax
 * Needs: 	Array with $array["url"] and $array["url"]
 * Returns: String

 */
function get_kirby_linksyntax( $link ){

	return "(link: ". $link["url"] . " text:" .$link["text"] . ")";
}

/**
 * Prüft, ob es ein spezielles Snipplet gibt. Falls nicht, wird das default Snip zurück gegeben.
 *
 * @author c.noss@klickmeister.de
 * @return Name des Templates
 */

function get_snip($uid, $default, $level = false){

	if(!$level){ $level = "03-organisms"; }
	$level = $level . "/";

	$default = preg_replace("=.*/=", "", $default);

	$tpl_core	= strtolower($uid);
	#$tpl_custom = strtolower($uid);

	$tpl = $uid;

	#if(!file_exists('site/snippets/'.$level.$tpl.'.php')){ 	$tpl_core 	= false; }
	#if(!file_exists('site/snippets/custom/'.$level.$tpl.'.php')){ 	$tpl_custom = false; }

	if($tpl_core){ 	$template = $level. $tpl; }
	else{				$template = $level. $default; }

	return $template;
}

function get_atom($uid){		return get_snip( $uid, false, "01-atoms");}
function get_molecule($uid){	return get_snip( $uid, false, "02-molecules");}
function get_organism($uid){	return get_snip( $uid, false, "03-organisms");}


/**
 * Baut ein Menü Objekt
 *
 * @author c.noss@klickmeister.de
 * @return Menü Objekt
 */


function make_menu_items($pages) {

	// alle Seiten holen
	$p = $pages->visible();

	// Rückgabeobjekt erzeugen
	$menu = array();

	// Wenn es Seiten gibt, dann diese abarbeiten
	if($p->count() > 0){
		foreach($p as $single_page){

			// Daten aufbereiten
			$data = array();
			$data["content"] = 	$single_page->title()->html();
			$data["active"] = 	"";if($single_page->isOpen()){ $data["active"] = 'class="active"';}
			$data["url"] = 		$single_page->url();

			// Markup erzeugen
			$item = array();
			$item["content"] = '<a '. $data["active"] .' href="'.$data["url"].'">'.$data["content"].'</a>';
			$item["class"] = "menu__item";
			array_push($menu, $item);
		}

	}

	return $menu;

};



/**
 * Baut eine Linkliste für Downloaddaten
 *
 * @author c.noss@klickmeister.de
 * @return List Objekt
 */


function make_dldata_list( $data_array ) {

	// Rückgabeobjekt erzeugen
	$list = array();

	// Datenarray abarbeiten
	foreach($data_array as $data){

			// Markup erzeugen
			$item = array();
			$item["content"] = '<a href="'.$data["url"].'"><span class="icon download '.$data["type"].'"></span>'.$data["name"].'</a>';
			array_push($list, $item);
		}

	return $list;

};




/**
 * Baut eine Taglist
 *
 * @author c.noss@klickmeister.de
 * @return list Objekt
 */


function make_tag_list( $tags ) {

	// Tags in Array
	$tag_array = explode(",", $tags);

	// Rückgabeobjekt erzeugen
	$list = array();

	// Datenarray abarbeiten
	foreach($tag_array as $tag){

			// Markup erzeugen
			$item = array();
			$item["content"] = '<a href="/search/tag:'.$tag.'">'.$tag.'</a>';
			array_push($list, $item);
		}

	return $list;

};



/**
 * Liefert die Bilder eines Artikels kategorisiert nach Typ
 *
 * @author c.noss@klickmeister.de
 * @return multidimensionales array mit bildern
 */

function get_images_from_article( $article, $prop = false, $images = false ){

    $bilder = array();
    $bilder["all"] = array();
    $bilder["prop"] = array();
    $bilder["svgs"] = array(); // Ehemals SVGs, wurden dann aber in PNGs konvertiert wegen Darstellungsproblemen bei Android
    $bilder["pixel"] = array();
    $bilder["lg"] = array();
    $bilder["thumbs"] = array();
    $bilder["thumbs-lg"] = array();
    $bilder["thumbs-lg-16zu9"] = array();
    $bilder["portrait"] = array();

    if(!$images){ $images = $article->images()->sortBy('sort', 'asc'); }

    // Alle Bilder abklappern
    foreach ($images as $img){

		$url 		= $img;
		$portrait 	= false;

		if(gettype($img) == "object"){
			$url 		= $img->url();
			$portrait 	= $img->isPortrait();

		}

		if( !preg_match("=\.jpg|\.svg|\.png|\.jpeg=", $url )){
			continue;
		}

		if( preg_match("=teaserpool/=", $url )){
			//$url = preg_replace("=.*teaserpool/=", "/_partner/_shared/content/uebergreifende-informationen/", $url);
		}

        switch (true) {
            case preg_match("=/icon.png=", $url):
                break;

            case preg_match("=/lg_=", $url):
                array_push($bilder["lg"], "/assets/php/timthumb/images.php?src=". $url ."&w=1200&q=80");
                break;

            case preg_match("=svg.png=", $url):
                array_push($bilder["svgs"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&q=80");
                array_push($bilder["all"],"/assets/php/timthumb/images.php?src=". $url."&w=1200&q=80");
                break;

            case $prop="proportional":
                array_push($bilder["pixel"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&q=80");
                array_push($bilder["all"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&q=80");
                array_push($bilder["prop"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&q=80");
                array_push($bilder["thumbs"], "/assets/php/timthumb/images.php?src=".$url ."&w=60&h=60&q=95&zc=1&s=1");
                array_push($bilder["thumbs-lg"], "/assets/php/timthumb/images.php?src=".$url ."&w=600&h=600&q=95&zc=1&s=1");
                array_push($bilder["thumbs-lg-16zu9"], "/assets/php/timthumb/images.php?src=".$url ."&w=600&h=330&q=95&zc=1&s=1");
                array_push($bilder["portrait"], ($portrait?"true":"false") );
                break;

            default:
                array_push($bilder["pixel"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&h=600&q=80");
                array_push($bilder["all"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&h=600&q=80");
                array_push($bilder["prop"], "/assets/php/timthumb/images.php?src=".$url."&w=1200&q=80");
                array_push($bilder["thumbs"], "/assets/php/timthumb/images.php?src=".$url ."&w=60&h=60&q=95&zc=1&s=1");
                array_push($bilder["thumbs-lg"], "/assets/php/timthumb/images.php?src=".$url ."&w=500&h=500&q=95&zc=1&s=1");
                array_push($bilder["thumbs-lg-16zu9"], "/assets/php/timthumb/images.php?src=".$url ."&w=600&h=330&q=95&zc=1&s=1");
                array_push($bilder["portrait"], "false");
        }
    }


    return $bilder;

}

/**
 * Liefert Downloaddokumente eines Artikels kategorisiert nach Typ
 *
 * @author c.noss@klickmeister.de
 * @return multidimensionales array mit downloaddaten
 */

function get_documents_from_article( $article, $prop = false ){

	$docs = array();
	$docs["all"] = array();
	$docs["pdf"] = array(); // Ehemals SVGs, wurden dann aber in PNGs konvertiert wegen Darstellungsproblemen bei Android
	$docs["zip"] = array();


	// Alle Bilder abklappern
	foreach ($article->files() as $file){

		$f = array();
		$f["url"] 	= $file->url();
		$f["name"] 	= $file->name();
		$f["type"] 	= $file->extension();

		if(preg_match("=pdf|zip=", $f["type"])){
			array_push($docs["all"], $f);
		}

		switch (true) {

			case preg_match("=pdf=", $f["type"]):
				array_push($docs["pdf"], $f );
				break;

			case preg_match("=zip=", $f["type"]):
				array_push($docs["zip"], $f );
				break;

		}
	}

	return $docs;

}

?>
