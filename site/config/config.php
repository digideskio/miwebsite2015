<?php

$index = dirname(__DIR__);
include_once($index.'/config/custom-config.php');

/*

---------------------------------------
License Setup
---------------------------------------

Please add your license key, which you've received
via email after purchasing Kirby on http://getkirby.com/buy

It is not permitted to run a public website without a
valid license key. Please read the End User License Agreement
for more information: http://getkirby.com/license

*/

c::set('license', $custom_config["kirby_license"]);

/*

---------------------------------------
Kirby Configuration
---------------------------------------

By default you don't have to configure anything to
make Kirby work. For more fine-grained configuration
of the system, please check out http://getkirby.com/docs/advanced/options

*/

c::set('debug', true);


c::set('languages', array(
  array(
    'code'    => 'de',
    'name'    => 'Deutsch',
    'default' => true,
    'locale'  => 'de_DE',
    'url'     => '/de',
  ),
));
c::set('language.detect', true);

/*
---------------------------------------
Minify output
---------------------------------------
*/
c::set('MinifyHTML', $custom_config["compress_html"]);

/*
---------------------------------------
Cache Dir
---------------------------------------
*/
c::set('cachedir', $custom_config["cachedir"]);

/*
---------------------------------------
Pattern lab
---------------------------------------
*/
c::set('patterns.title', 'Patterns');
c::set('patterns.path', 'patterns');
c::set('patterns.directory', $custom_config["base_path"].'/site/patterns');
c::set('patterns.lock', false);
c::set('patterns.preview.css', 'assets/css/index.css');
c::set('patterns.preview.js', 'assets/js/index.js');
c::set('patterns.preview.background', false);

/*
---------------------------------------
Teaser snips, um bootstrap 4 oder 3 verwenden zu können
---------------------------------------
*/
c::set('atomic-snippet–prefix', "atomic/");
c::set('teaser-classes', array(
  "teaser-overview"       => "card-columns teaser-overview",
  "teaser-item"           => "card teaser--item",
  "teaser-item-template"  => "card",
  "wrap"                  => true,
  "href"                  => true // Soll ein href angezeigt werden? oder werden die Teaser via Ajax geladen
));


/*
---------------------------------------
Icon shortcuts
---------------------------------------
*/

c::set('icons', array(
  "link" => "fa-angle-double-right",
  "hyperlink" => "fa-external-link"

));



/*
---------------------------------------
Zentrale Links
---------------------------------------
*/

c::set('links', array(
  "bewerbung-bachelor" => "https://www.th-koeln.de/studium/medieninformatik-bachelor--bewerbung_3962.php",
  "bachelor" => "/bachelor"
));
