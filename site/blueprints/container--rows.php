<?php if(!defined('KIRBY')) exit ?>

title: Complete Row (Container)
pages:
  template:
  	- content--article
  	- content--bighead
  	- content--video
  	- content--article-overview
  	- content--team-overview
  	- content--event-overview
  	- content--slideshow
  	- content--form-builder
  	- container--rows
  	- container--component
files:
	sortable: true
fields:
  info:
  	label: Complete Row (Container)
  	type: info
  	text: This template shows all subpages in a own row.
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

  trennlinie:
    label: Horizontal Ruler
    type: radiosingleton
    width: 1/2
    options:
      hide: hide
      show: show

  behavior_type:
    label: Behavior
    type: radiosingleton
    options:
      standard: standard
      accordion-closed: accordion, closed
      accordion-opened: accordion, opened
