<?php

class MainCSS {

    // Path to the cache file
    private $cached_css_filepath = "main-css.css";

    // Path to the main scss file
    private $main_scss_filepath = "main-css.scss";

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

        // Updating the cache file path
        if(isset($config['cachedir'])) {
            $this->cached_css_filepath = $config["cachedir"]. "/" .
                                            $this->cached_css_filepath;
        }

        // Removing non-existent directories
        $this->scss_level_directories = array_filter( $this->scss_level_directories,
                                                      function($dir) {
                                                          return file_exists($dir);
                                                      } );

        // We also want to check the modification time of the parent dir (./scss)
        $scss_level_directories_with_parent = array_merge( array($this->scss_base_dir),
                                                           $this->scss_level_directories );

        // Get the modification time of all directories
        $scss_level_directories_mtimes = array_map(function($dir) {
            return filemtime($dir);
        }, $scss_level_directories_with_parent);

        // Any updates?
        $latest_mtime = max($scss_level_directories_mtimes);

        if( !$this->css_cache_valid($latest_mtime) ) {
            // Refresh css cache
            $this->refresh_css_cache();
        }

        // Serving the cache
        $this->serve_css_cache();
    }


    private function css_cache_valid($latest_mtime) {
        $cached_css_valid = file_exists($this->cached_css_filepath)
                            &&  $latest_mtime < filemtime($this->cached_css_filepath);

        $main_scss_valid = file_exists($this->main_scss_filepath)
                            &&  $latest_mtime < filemtime($this->main_scss_filepath);

        return $cached_css_valid && $main_scss_valid;
    }


    private function refresh_css_cache() {
        // Clearing the CSS cache
        $this->clear_cache();

        // Getting scss files
        $scss_stack = array();
        foreach($this->scss_level_directories as $dir) {
            $scss_stack = array_merge( $scss_stack, glob($dir . "/*.scss") );
        }

        // Generating the main scss file
        $main_scss_content = "";
        foreach($this->import_directories as $file){
            $main_scss_content .= "@import '" . $file . "';\n";
        }

        foreach($scss_stack as $file){
            $main_scss_content .= "@import '" . $file . "';\n";
        }

        $success = @file_put_contents($this->main_scss_filepath,
                                      $main_scss_content);

        if(!$success)
            $this->not_found_response("Could not update: '".
                                        $this->main_scss_filepath ."'");

        // Generate the CSS
        require_once '../lib/scssphp/scss.inc.php';

        $scss = new Leafo\ScssPhp\Compiler();
        $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');

        try {
            $css = $scss->compile('@import "'. $this->main_scss_filepath .'";');

            $success = @file_put_contents($this->cached_css_filepath, $css);

            if(!$success)
                throw new Exception("Could not update: '".
                                        $this->cached_css_filepath ."'");
        }
        catch(Exception $e) {
            // Responding with the exception message
            $this->not_found_response($e->getMessage());
        }
    }


    private function clear_cache() {
        // Clear CSS cache

        if(file_exists($this->cached_css_filepath))
            unlink($this->cached_css_filepath);
    }


    private function serve_css_cache() {
        $css = @file_get_contents($this->cached_css_filepath);
        $this->ok_response($css);
    }


    private function ok_response($body_content) {
        header('Content-Type: text/css');
        echo $body_content;
        die();
    }


    private function not_found_response($body_content) {
        $server_protocol = isset($_SERVER['SERVER_PROTOCOL']) ?
                                $_SERVER['SERVER_PROTOCOL']:
                                "HTTP/1.1";
        header($server_protocol." 404 Not Found");
        echo $body_content;
        die();
    }
}



include_once('../../config/custom-config.php');

$maincss = new MainCSS($custom_config);

