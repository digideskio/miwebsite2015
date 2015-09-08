<?php

class structhelper {

    private function __construct() {} // Do not allow instantiation


    /**
     * Holt die Kinder einer Page
     * 
     * @author c.noss@klickmeister.de
     * @return Container Objekte 
     */
    public static function get_container($site, $pages, $page) {
	    // get all articles
	    $containers = $page->children()->visible();
	    return $containers;
    }


	/**
     * Holt die Kinder der Blog-Page
     * 
     * @author c.noss@klickmeister.de
     * @return Container Objekte der Blog-Page 
     */
    public static function get_blog_container($site, $pages, $page, $limit) {
	    $containers = $pages->find("blog")->children()->visible()->flip()->limit($limit)->sortBy('date', 'desc');//->visible();
	    return $containers; 
    }


    /**
     * Returns a String in Kirbytext Linksyntax
     * Needs: 	Array with $array["url"] and $array["url"]
     * Returns: String
     */
    public static function get_kirby_linksyntax( $link ){
	
	    return "(link: ". $link["url"] . " text:" .$link["text"] . ")";	
    }


    /**
     * Baut ein Menü Objekt
     * 
     * @author c.noss@klickmeister.de
     * @return Menü Objekt 
     */
    public static function make_menu_items($pages) {
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
    }


    /**
     * Baut eine Linkliste für Downloaddaten
     * 
     * @author c.noss@klickmeister.de
     * @return List Objekt 
     */
    public static function make_dldata_list( $data_array ) {
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
    }


    /**
     * Baut eine Taglist
     * 
     * @author c.noss@klickmeister.de
     * @return list Objekt 
     */
    public static function make_tag_list( $tags ) {
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
    }


    /**
     * Liefert die Bilder eines Artikels kategorisiert nach Typ
     * 
     * @author c.noss@klickmeister.de
     * @return multidimensionales array mit bildern 
     */
    public static function get_images_from_article( $article, $prop = false ){
	    $images = array();
	    $images["all"] = array();
	    $images["svgs"] = array(); // Ehemals SVGs, wurden dann aber in PNGs konvertiert wegen Darstellungsproblemen bei Android
	    $images["pixel"] = array();
	    $images["lg"] = array();
	    $images["thumbs"] = array();

	    /* For a parameter overview see http://www.binarymoon.co.uk/2012/02/complete-timthumb-parameters-guide/ */
	    $timthumb_params = array();
	    $timthumb_params['normal'] = array(
            'w' => self::get_or_else( c::get('sh.timthumb.normal.width')  , 800 ),
            'h' => self::get_or_else( c::get('sh.timthumb.normal.height') , 600 ),
            'q' => self::get_or_else( c::get('sh.timthumb.normal.quality'),  80 )
        );
	    $timthumb_params['thumb'] = array(
            'w'  => self::get_or_else( c::get('sh.timthumb.thumb.width')   , 60 ),
            'h'  => self::get_or_else( c::get('sh.timthumb.thumb.height')  , 60 ),
            'q'  => self::get_or_else( c::get('sh.timthumb.thumb.quality') , 95 ),
            'zc' => self::get_or_else( c::get('sh.timthumb.thumb.zoomcrop'),  1 ),
            's'  => self::get_or_else( c::get('sh.timthumb.thumb.sharpen') ,  1 )
        );

	    // go through each image
	    foreach ($article->images() as $img){
		    switch (true) {
			    case preg_match("=/icon_=", $img):
				    $images["icon"] = $img->url();
				    break;
				
			    case preg_match("=/lg_=", $img):
				    $images["lg"][] = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal'], array('h')); /* Exclude height */
				    break;
				
			    case preg_match("=svg.png=", $img):
				    $images["svgs"][] = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal'], array('h')); /* Exclude height */
				    $images["all"][]  = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal'], array('h')); /* Exclude height */
				    break;
			
			    case $prop:
				    $images["pixel"][]  = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal'], array('h')); /* Exclude height */
				    $images["all"][]    = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal'], array('h')); /* Exclude height */
				    $images["thumbs"][] = self::construct_timthumb_path_url($img->url(), $timthumb_params['thumb']);
				    break;
			
			    default:
				    $images["pixel"][]  = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal']);
				    $images["all"][]    = self::construct_timthumb_path_url($img->url(), $timthumb_params['normal']);
				    $images["thumbs"][] = self::construct_timthumb_path_url($img->url(), $timthumb_params['thumb']);
		    }
	    }

	    return $images;
    }


    /**
     * Liefert Downloaddokumente eines Artikels kategorisiert nach Typ
     * 
     * @author c.noss@klickmeister.de
     * @return multidimensionales array mit downloaddaten 
     */
    public static function get_documents_from_article( $article, $prop = false ){
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

    /**
     * Ist der Wert gesetzt und "wahr", wird er zurückgegeben, ansonsten der Default-Wert
     *
     * @author
     * @param $val      Zu überprüfender Wert
     * @param $else_val Default-Wert auf den zurückgefallen wird
     * @return          Gültiger Wert
     */
    private static function get_or_else($val, $else_val) {
        return $val ? $val: $else_val;
    }

    /**
     * Konstruiert den Pfadanteil der URL zum timthumb-Script samt Parameter
     *
     * @author
     * @param $src_url              URL zur Grafikdatei
     * @param $params               Assoziatives Array mit den zu übergebenden GET-Parametern, wobei Schlüssel als Parameternamen fungieren
     * @param $exclude_param_keys   Liste von Parameternamen, die ausgeschlossen werden sollen
     * @return                      Konstruierter Pfadanteil zum timthumb-Script
     */
    private static function construct_timthumb_path_url($src_url, $params = array(), $exclude_param_keys = array()) {
        if(!c::get('sh.timthumb.urlpath')) {
            throw new Exception("Config key 'sh.timthumb.urlpath' is not set!");
        }

        /* Exclude unneeded params  */
        $params = array_filter( $params,
                                function($key) use($exclude_param_keys) {
                                    return !in_array($key, $exclude_param_keys);
                                },
                                ARRAY_FILTER_USE_KEY);

        $concatenated_params_arr = array_map( function($key, $val) {
                                             return $key . "=" . $val;
                                          },
                                          array_keys($params),
                                          $params);

        return c::get('sh.timthumb.urlpath') . "?src=" . $src_url . '&' . implode($concatenated_params_arr, '&');
    }
}
