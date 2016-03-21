/* Config
------------------------------------------------------*/


/* Functions
------------------------------------------------------*/

/* Ajax Nachladerei
############################################ */
var last_tooltip = false;

function after_ajax( ele ){

	if(picturefill()){ picturefill(); }
	$(window).lazyLoadXT();


//	link_handling.func.init(ele);

	// Alle Ajax trigger abklappern
	$('[data-handle=ajax]').each(function(){

		function close( event ){
			event.preventDefault();

			// … entfernen den Handler, damit die Aktion nicht noch mal aufgerufen werden kann
			$this.unbind("click");

			// … klappen das Div zu und schmeißen es nachher weg
			$this.closest(".product-detail").find(".ajaxed_content").slideUp(400, function() {
				$(this).remove();
				$this.removeClass("loaded");
			});

			// Sagen dem Trigger, dass er wieder triggern darf
			$this.attr("data-handle","ajax");

			// Initiaslisieren den nachgeladenen Content
			after_ajax();
		}

		var $this = $(this);

		// Damit nicht mehrere Events gelegt werden, entfernen wir den handle
		$this.removeAttr("data-handle");

		$(this).click(function(event){
			event.preventDefault();

			// Wir holen uns die notwendigen Daten
			var real_url = $this.attr("href").match(/(.*)\/(.*)/);

			// Pfad der Elternseite
			var path = RegExp.$1;

			// UID der Seite
			var id = RegExp.$2;

			// Das zu verwendende Template
			var template = $this.attr("data-template");

			// Optional: Eine aktion, z.B. das löschen des Triggers
			var aktion = $this.attr("data-aktion");

			// Optional: Einen Index für die Slideshow... !pe
			var idx = $(event.target).index();

			// Darum bauen wir eine schöne URL
			var ajax_url = "ajax?id="+id+"&path="+path + "&template="+template + "&idx="+idx;

			// Wir holen den Content …
			$.get( ajax_url , function( data ) {

				// … geben ihm ein Div und packen ihn da rein
				$this.closest(".product-detail").append( "<div class='ajaxed_content'>"+data+"</div>" );

				// … sagen dem Trigger, dass der COntent da ist
				$this.addClass("loaded");

				// … entfernen den Handler, damit die Aktion nicht noch mal aufgerufen werden kann
				$this.unbind("click");

				// … überwachen das Schließen des Contents
				$this.click(function(event){ close(event); });
				$(".close_ajaxed").click(function(event){ close(event); });

				if(aktion && aktion == "close"){
					$this.remove();
				}
				after_ajax( $(".ajaxed_content") );
			});

		});
	});
}


/*  Wir laden teils Artikel via Ajax nach, die müssen beim Resize ggf angepasst werden.
    Bei der MI setzen wir aber nur einen Link auf den Artikel.
############################################ */
var ajaxed_content = {};
ajaxed_content.data = {};
ajaxed_content.data.active_content = false;
ajaxed_content.func = (function(){
	var exports = {};

	exports.toggleitem = function(caller, uid, scrolltarget, tpl){

    uid.match(/(.*)\/(.*)/);
		var path = RegExp.$1;
		var uid = RegExp.$2;
    var url = path + "#" + uid;

    location.href = url;

		//return false;
	}



	return exports;
})();





/* Anzeigemodus detectieren
############################################ */

var detect_viewmode = (function(){

	// lokale Variablen
	var viewmode 		= "xs",
		exports 		= {};

	// lokale Funktionen
	function get_viewmode(){
		if($(".detect.visible-xs-block").is(':visible')){ viewmode = "xs"; }
		if($(".detect.visible-sm-block").is(':visible')){ viewmode = "sm"; }
		if($(".detect.visible-md-block").is(':visible')){ viewmode = "md"; }
		if($(".detect.visible-lg-block").is(':visible')){ viewmode = "lg"; }
		//this.viewmode = viewmode;
		return viewmode;
	}

	// öffentliche Funktionen und init
	exports.get = function(){
		return get_viewmode();
	};

	// öffentliche Funktionen bekanntgeben
	return exports;
})();





/* Falls Positionen optimiert werden müssen
############################################ */

var fix_positions = {};
fix_positions.data = {};
fix_positions.func = (function(){
	var exports = {};

	/* größe der einzelnen Elemente holen */
	function get_data(){
		fix_positions.data["header"] 		= $("header").height();
		fix_positions.data["xs-sm-head"] 	= $("#xs-sm-head").height();
		fix_positions.data["viewport"] 		= $(window).height();
		fix_positions.data["viewport-w"] 	= $(window).width();
		fix_positions.data["bighead"] 		= $(".bighead img").height(); //($(".bighead img").height()) ? $(".bighead img").height() : 600;
		fix_positions.data["viewport-ratio"]= fix_positions.data["viewport-w"] / fix_positions.data["viewport"];
	}

	exports.init = function( ){
		get_data();
	}

	return exports;
})();



/* Header Minifizierung
############################################ */

var minify_header = {};
minify_header.data = {}
minify_header.func = (function(){
	var exports = {};

  function do_minify(){

    $('.header--tiny').toggleClass('sichtbar', $(document).scrollTop() > minify_header.data.header_height)

  }

	exports.init = function( ){

    minify_header.data.header_height = $('.page-header').height();
    do_minify();
    $(window).on("scroll touchmove", do_minify);

	}

	return exports;
})();


/* Main
------------------------------------------------------*/


$(window).load(function () {

	// Wenn der Subhead gezeigt wird, muss der Abstand des Contents entsprechend groß sein
	//if($("#sub-head").length > 0){ $("body").addClass("subhead"); }

	fix_positions.func.init(); //window.setTimeout(fix_positions.func.init, 500);

	var resizeId; $(window).resize(function() { clearTimeout(resizeId); resizeId = setTimeout(doneResizing, 500); });
	function doneResizing(){
		fix_positions.func.init();
		var ajaxed_article = document.getElementsByClassName("ajaxed_article");
		if(document.getElementsByClassName("ajaxed_article").length > 0) ajaxed_content.func.rearrange();
	}

	// gibt es sprungmarken?
	//fix_scroll_position.init(); // window.setTimeout(fix_scroll_position.init, 1500);

  minify_header.func.init();


  $("#mi-box-tiny").click(function(){
      $("html, body").animate({ scrollTop: 0 }, "slow");
  });



	after_ajax();
});
