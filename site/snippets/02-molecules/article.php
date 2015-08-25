<!-- Molecule: article -->
<article>
	<div class="head">
		<?php 
		if($content->headline() == ""){
			atomicdesign::output("atom", "headline", array("text" => $content->title()));
		}else{
			atomicdesign::output("atom", "headline", array("text" => $content->headline()));
		}
		
		if($content->subheadline() != ""){
			atomicdesign::output("atom", "subheadline", array("text" => $content->subheadline()));
		}
		?>
	</div>
	
	<div class="body">
		<?php	
		if(isset($excerpt)){
			atomicdesign::output("atom", "text", array("text" => $excerpt));	
			
		}else if($content->text() != ""){
			atomicdesign::output("atom", "text", array("text" => $content->text()));	
		} 
		?>
		
		<?php
		// Do we have a URL and a linkname?
		if(isset($link["text"])){ 
			atomicdesign::output("atom", "text", array("text" => structhelper::get_kirby_linksyntax($link)));
		
		// or do we have a link only?
		}else if(isset($link)){
			atomicdesign::output("atom", "text", array("text" => $link));
		}
		?>
	</div>
	
	<?php if(isset($docs) && sizeof($docs["all"]) >0): ?>
	<div class="documents">
		
		<?php
			$items = structhelper::make_dldata_list($docs["all"]);
			atomicdesign::output("atom", "list-unordered", array("items" => $items, "class" => "download-list" )); 
		?>
		
	</div>
	<?php endif; ?>
	
	<div class="foot">
		<?php
		if($content->date() != ""){
			atomicdesign::output("atom", "date", array("date" => $content->date()));
		}
		
		if($content->autor() != ""){
			atomicdesign::output("atom", "autor", array("text" => $content->autor()));
		}
		
		if($content->tags() != ""): ?>
			
		<!--div class="tag-list-wrap">
			<span class="glyphicon glyphicon-tags"></span>
			<?php
				$items = structhelper::make_tag_list($content->tags());
				atomicdesign::output("atom", "list-unordered", array("items" => $items, "class" => "tag-list" ));
			?>
		</div-->
		<?php endif; ?>
	</div>
</article>
