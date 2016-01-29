<?php if(!defined('KIRBY')) exit ?>

title: Base (Container)
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
    - container--cols
files:
	sortable: true;
fields:
  info:
  	label: Basis (Container)
  	type: info
  	text: This is the base template. Subpages are allowed to use content or container templates.
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
  invisible:
    label: Invisible Data
    type: headline
  meta-title:
    label: Title Extension (max. 55 characters)
    type:  text
  keywords:
    label: Meta-Keywords
    type:  text
  desc:
    label: Meta-Description
    type:  text
  facebook-title:
    label: Facebook Title
    type:  text
  facebook:
    label: Facebook Text
    type:  textarea
  layout:
    label: Design (only editable in your main language)
    type: headline

  trennlinie:
    label: Horizontal Ruler
    type: radiosingleton
    width: 1/2
    options:
      hide: hide
      show: show

  subhead:
    label: Sub Headline
    type: radiosingleton
    width: 1/2
    options:
      hide: hide
      show: show

  padding_at_top:
    label: Padding
    width: 1/2
    type: checkboxteaser
    text: add padding at top

  freigabelinie:
    label: Decontrol (only editable in your main language)
    type: headline

  freigabe:
    label: Decontrol
    type: radioadmin
    width: 1/2
    options:
      false: none
      true: request
