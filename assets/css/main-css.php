<?php

class MainCSS {

    // Filename for persistent cache
    private $css_cached_lumm = "style.css";

    // Filename of the main less file
    private $main_less_filename = "main-css.less";

    // Less base directory
    private $less_base_dir = "./less";

    // Core variables and mixins
    private $import_directories = array(
        "./less/variables_bootstrap.less",
        "../lib/bootstrap/less/mixins.less",
        "./less/variables_custom.less"
    );

    // AtomicDesign level less directories with containing less files
    private $less_level_directories = array(
        "./less/00-base",
        "./less/01-atoms",
        "./less/02-molecules",
        "./less/03-organisms"
    );


    // Config array
    private $config = array();
    
    // Filepath given by Less_Cache
    private $cached_css_filepath = "";


    public function __construct($config = array()) {

        $this->config = $config;

        // Updating the lumm cache file path
        if(isset($config['cachedir'])) {
            $this->css_cached_lumm = $config["cachedir"]."/" .$this->css_cached_lumm;
        }

        // Removing non-existent directories
        $this->less_level_directories = array_filter($this->less_level_directories, function($dir) {
            return file_exists($dir);
        });

        // We also want to check the modification time of the parent dir (./less)
        $less_level_directories_with_parent = array_merge( array($this->less_base_dir), $this->less_level_directories );

        // Get the modification time of all directories
        $less_level_directories_mtimes = array_map(function($dir) {
            return filemtime($dir);
        }, $less_level_directories_with_parent);

        // Any updates?
        $latest_mtime = max($less_level_directories_mtimes);

        // Checking if a up-to-date lumm cache exists
        if( $this->lumm_cache_valid($latest_mtime) ) {
            // Serving the lumm cache
            $this->serve_lumm_cache();
        }
        else {
            if( !$this->less_cache_valid($latest_mtime) ) {
                // Refresh less cache
                $this->refresh_less_cache();
            }
            else {
                $this->not_found_response("Less Cache is still valid!");
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
        $content = file_get_contents($this->css_cached_lumm);
        $this->ok_response($content);
    }


    private function refresh_lumm_cache() {
        $css = file_get_contents( $this->cached_css_filepath );
        
        // Create a more persistent cache file
        file_put_contents($this->css_cached_lumm, $css);
    }


    private function less_cache_valid($latest_mtime) {
        return      file_exists($this->main_less_filename)
                &&  $changes < filemtime($this->main_less_filename);
    }


    private function clear_less_cache() {
        // Clear less cache
        $cached_files = array_filter(glob( $this->config["cachedir"] ), 'is_file');

        foreach($cached_files as $file){
            unlink($file);
        }
    }


    private function refresh_less_cache() {
        // Clearing the less cache
        $this->clear_less_cache();

        // Getting less files
        $less_stack = array();
        foreach($this->less_level_directories as $dir) {
            $less_stack = array_merge( $less_stack, glob($dir . "/*.less") );
        }

        // Generating the main less file
        $main_less_content = "";
        foreach($this->import_directories as $file){    $main_less_content .= "@import '" . $file . "';\n"; }
        foreach($less_stack as $file){                  $main_less_content .= "@import '" . $file . "';\n"; }
        file_put_contents($this->main_less_filename, $main_less_content);
        
        // Generate the CSS
        require_once "../lib/less.php/Less.php";

        try{
            $less_files = array( $this->main_less_filename => '/' );
    
            $options = array(
                'compress'          => true,
                'sourceMap'         => true,
                'sourceMapWriteTo'  => './sourcemaps/main.map',
                'sourceMapURL'      => './sourcemaps/main.map',
                'cache_dir'         => $this->config["cachedir"]
            );

            $css_file_name = Less_Cache::Get( $less_files, $options );
            $this->cached_css_filepath = $this->config["cachedir"]."/".$css_file_name;
            
        } catch(Exception $e){
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

