<?php if(!defined('KIRBY')) exit ?>
title: Site
pages: 
	template: 
		- container--basis
fields:
  visible_section:
    label: Sichtbare Angaben
    type: headline
  title:
    label: Title
    type:  text
  author:
    label: Author
    type:  text
  copyright:
    label: Copyright
    type:  text
    