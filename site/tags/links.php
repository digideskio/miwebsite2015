<?php

kirbytext::$tags['link'] = array(
	'attr' => array(
    	'icon', 'text'
	),
	'html' => function($tag) {
		$text = $tag->attr('text');
		$url 	= $tag->attr('link');
		$icon = $tag->attr('icon', false);

    // Wurde ein Shortcut hinterlegt?
		$icons =  c::get("icons");
		$icon = (isset($icons[$icon])) ? $icons[$icon] : $icon;

		$html = '<a href="'.$url.'">';
		if($icon){ $html .= '<i class="fa '.$icon.'"></i> '; }
		$html .= $text;
		$html .= '</a>';

		return $html;
	}
);

kirbytext::$tags['shortcut'] = array(
	'attr' => array(
    	'icon', 'text'
	),
	'html' => function($tag) {
		$text = $tag->attr('text');
		$shortcut 	= $tag->attr('shortcut');
		$icon = $tag->attr('icon', false);

    // Wurde ein Shortcut hinterlegt?
		$icons =  c::get("icons");
		$icon = (isset($icons[$icon])) ? $icons[$icon] : $icon;

		// Wurde ein Link hinterlegt?
		$links =  c::get("links");
		$url = (isset($links[$shortcut])) ? $links[$shortcut] : $shortcut;

		$html = '<a href="'.$url.'">';
		if($icon){ $html .= '<i class="fa '.$icon.'"></i> '; }
		$html .= $text;
		$html .= '</a>';

		return $html;
	}
);

?>
