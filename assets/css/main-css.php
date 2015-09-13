<?php

class MainCSS {

    // Filename for persistent cache
    private $css_cached_lumm = "style.css";

    // Filepath given by scss_Cache
    private $cached_css_filepath = "main-css.css";

    // Filename of the main scss file
    private $main_scss_filename = "main-css.scss";

    // scss base directory
    private $scss_base_dir = "./scss";

    // Core variables and mixins
    private $import_directories = array(
        "./scss/variables_bootstrap.scss",
        "../lib/bootstrap/assets/stylesheets/bootstrap/mixins.scss",
        "./scss/variables_custom.scss"
    );

    // AtomicDesign level scss directories with containing scss files
    private $scss_level_directories = array(
        "./scss/00-base",
        "./scss/01-atoms",
        "./scss/02-molecules",
        "./scss/03-organisms"
    );


    // Config array
    private $config = array();
    

    public function __construct($config = array()) {

        $this->config = $config;

        // Updating the lumm cache file path
        if(isset($config['cachedir'])) {
            $this->css_cached_lumm = $config["cachedir"]."/" .$this->css_cached_lumm;
        }

        // Removing non-existent directories
        $this->scss_level_directories = array_filter($this->scss_level_directories, function($dir) {
            return file_exists($dir);
        });

        // We also want to check the modification time of the parent dir (./scss)
        $scss_level_directories_with_parent = array_merge( array($this->scss_base_dir), $this->scss_level_directories );

        // Get the modification time of all directories
        $scss_level_directories_mtimes = array_map(function($dir) {
            return filemtime($dir);
        }, $scss_level_directories_with_parent);

        // Any updates?
        $latest_mtime = max($scss_level_directories_mtimes);

        // Checking if a up-to-date lumm cache exists
        if( $this->lumm_cache_valid($latest_mtime) ) {
            // Serving the lumm cache
            $this->serve_lumm_cache();
        }
        else {
            if( !$this->scss_cache_valid($latest_mtime) ) {
                // Refresh scss cache
                $this->refresh_scss_cache();
            }
            
            $this->refresh_lumm_cache();
            
            // Serving the lumm cache right after it was refreshed
            $this->serve_lumm_cache();
        }
    }


    private function lumm_cache_valid($latest_mtime) {
        return      file_exists($this->css_cached_lumm)
                &&  $latest_mtime < filemtime($this->css_cached_lumm);
    }


    private function serve_lumm_cache() {
        $content = @file_get_contents($this->css_cached_lumm);
        $this->ok_response($content);
    }


    private function refresh_lumm_cache() {
        $css = @file_get_contents( $this->cached_css_filepath );

        // Create a more persistent cache file
        $success = @file_put_contents($this->css_cached_lumm, $css);

        if(!$success) {
            $this->not_found_response("Could not refresh lumm cache: '". $this->css_cached_lumm ."'");
            die();
        }
    }


    private function scss_cache_valid($latest_mtime) {
        return      (       file_exists($this->cached_css_filepath)
                        &&  $latest_mtime < filemtime($this->cached_css_filepath))
                &&  (       file_exists($this->main_scss_filename)
                        &&  $latest_mtime < filemtime($this->main_scss_filename));
    }


    private function clear_scss_cache() {
        // Clear scss cache
        $cached_files = array_filter(glob( $this->config["cachedir"] . '/*' ), 'is_file');

        foreach($cached_files as $file){
            unlink($file);
        }
    }


    private function refresh_scss_cache() {
        // Clearing the scss cache
        $this->clear_scss_cache();

        // Getting scss files
        $scss_stack = array();
        foreach($this->scss_level_directories as $dir) {
            $scss_stack = array_merge( $scss_stack, glob($dir . "/*.scss") );
        }

        // Generating the main scss file
        $main_scss_content = "";
        foreach($this->import_directories as $file){    $main_scss_content .= "@import '" . $file . "';\n"; }
        foreach($scss_stack as $file){                  $main_scss_content .= "@import '" . $file . "';\n"; }
        $success = @file_put_contents($this->main_scss_filename, $main_scss_content);

        if(!$success) {
            $this->not_found_response("Could not update: '". $this->main_scss_filename ."'!");
            die();
        }

        // Generate the CSS
        require_once '../lib/scssphp/scss.inc.php';

        $scss = new Leafo\ScssPhp\Compiler();
        $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
        
        try {
            $css = $scss->compile('@import "'. $this->main_scss_filename .'";');

            $this->cached_css_filepath = $this->config["cachedir"]."/".$this->cached_css_filepath;
            $success = @file_put_contents($this->cached_css_filepath, $css);

            if(!$success)
                throw new Exception("Could not update: '". $this->cached_css_filepath ."'");
        }
        catch(Exception $e) {
            // Responding with the exception message
            $this->not_found_response($e->getMessage());
        }
    }


    private function ok_response($content) {
        header('Content-Type: text/css');
        echo $content;
    }


    private function not_found_response($msg) {
        $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL']: "HTTP/1.1";
        header($server_protocol." 404 Not Found");
        echo $msg;
    }
}



include_once('../../config/custom-config.php');

$maincss = new MainCSS($custom_config);

