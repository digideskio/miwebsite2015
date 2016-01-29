<?php if(!defined('KIRBY')) exit ?>

title: Multiple Cols (Container)
pages:
  template:
  	- container--rows
  	- content--article
  	- content--bighead
  	- content--video
  	- content--article-overview
  	- content--team-overview
  	- content--event-overview
  	- content--slideshow
  	- content--form-builder
files:  false
fields:
  info:
  	label: Multiple Cols (Container)
  	type: info
  	text: This template shows all subpages in a own column
  	width: 1/2
  visible:
    label: Visible Data
    type: headline
  title:
    label: Title/ Headline
    type:  text
  hide-in-lang:
    label: Content in this language should be…
    type: radio
    width: 1/2
    default: false
    options: 
    	false: visible
    	true: invisible
  layout:
    label: Design (only editable in your main language)
    type: headline
        	
  headline_position:
    label: Headline
    type: radiosingleton
    options:
      hide: hide
      text: show above text
      subhead: show in subheader
      row: show in row above