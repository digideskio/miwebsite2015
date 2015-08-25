<?php

class atomicdesign {
    
    public static $levelmap = array(
        'atom'      => '01-atoms',
        'molecule'  => '02-molecules',
        'organism'  => '03-organisms'
    );
    
    private function __construct() {} // Do not allow instantiation
    
    /**
     * Prüft, ob es ein spezielles Snipplet gibt. Falls nicht, wird das default Snip zurück gegeben.
     * 
     * @author c.noss@klickmeister.de
     * @param $uid      Eindeutiger Name des Ebenen-Blocks
     * @param $default  Name des Atoms, auf welchen ausgewichen werden soll
     * @param $level    Ordnername der AtomicDesign-Ebene
     * @return          Name des Templates 
     */
    public static function get_snip( $uid, $default, $level = false ) {
        if(!$level){ $level = static::$levelmap['organism']; }
        $level = $level . "/";

        $default = preg_replace("=.*/=", "", $default);

        $tpl_core	= strtolower($uid);
        #$tpl_custom = strtolower($uid);

        $tpl = $uid;

        #if(!file_exists('site/snippets/'.$level.$tpl.'.php')){ 	$tpl_core 	= false; }
        #if(!file_exists('site/snippets/custom/'.$level.$tpl.'.php')){ 	$tpl_custom = false; }

        if($tpl_core){  $template = $level. $tpl; }
        else{			$template = $level. $default; }

        return $template;
    }
    
    /**
     * Übergbt der 'snippet'-Funktion den Pfad zu einem AtomicDesign-
     * 
     * @author 
     * @param $level    Name der AtomicDesign-Ebene (vereinfacht)
     * @param $uid      Eindeutiger Name eines existierenden Ebenen-Blocks
     * @param $vars     Array von Variablen, die im Snippet Verwendung finden
     * @param $return   Rückgabe des konstruierten Snippet aus AtomicDesign-Ebenen-Blocks
     * @return          Konstruiertes Snippet aus Atomic-Ebenen-Blocks
     */
    public static function output( $level, $uid, $vars = array(), $return = false ) {
        if(!isset(self::$levelmap[$level])) {
            throw new Exception("AtomicDesign-Level not found: ".$level);
        }
        
        $template = self::get_snip($uid, false, self::$levelmap[$level]);
        
        return snippet($template, $vars, $return); // the function 'snippet' is introduced by kirby
    }
}

