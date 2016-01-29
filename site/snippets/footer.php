<section class="container content trenner">
	<div id="kontakt-content">

		<div class="text centered">
			<h3>&nbsp;</h3>
			<p>Wir freuen uns über Ihre Meinung <a href="mailto:&#x70;&#120;&#x72;&#x40;&#100;&#101;&#117;&#x74;&#x73;&#x63;&#x68;&#x65;&#98;&#x61;&#x68;&#x6e;&#x2e;&#x63;&#111;&#x6d;">&#x70;&#120;&#x72;&#x40;&#100;&#101;&#117;&#x74;&#x73;&#x63;&#x68;&#x65;&#98;&#x61;&#x68;&#x6e;&#x2e;&#x63;&#111;&#x6d;</a></p><!-- Snippet: yy -->
		</div>

	</div>
</section>

<div class="container content">
	<footer>
		<?php echo $site->copyright()->kirbytext() ?>
	</footer>
</div>

<div class="detect visible-xs-block"></div>
<div class="detect visible-sm-block"></div>
<div class="detect visible-md-block"></div>
<div class="detect visible-lg-block"></div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="/assets/lib/bootstrap/dist/js/bootstrap.min.js"></script>
<script src="/assets/lib/blueimp/js/blueimp-gallery.min.js"></script>

<script type="text/javascript">

var mobile = false;
(function(i){var e=/iPhone/i,n=/iPod/i,o=/iPad/i,t=/(?=.*\bAndroid\b)(?=.*\bMobile\b)/i,r=/Android/i,d=/BlackBerry/i,s=/Opera Mini/i,a=/IEMobile/i,b=/(?=.*\bFirefox\b)(?=.*\bMobile\b)/i,h=RegExp("(?:Nexus 7|BNTV250|Kindle Fire|Silk|GT-P1000)","i"),c=function(i,e){return i.test(e)},l=function(i){var l=i||navigator.userAgent;this.apple={phone:c(e,l),ipod:c(n,l),tablet:c(o,l),device:c(e,l)||c(n,l)||c(o,l)},this.android={phone:c(t,l),tablet:!c(t,l)&&c(r,l),device:c(t,l)||c(r,l)},this.other={blackberry:c(d,l),opera:c(s,l),windows:c(a,l),firefox:c(b,l),device:c(d,l)||c(s,l)||c(a,l)||c(b,l)},this.seven_inch=c(h,l),this.any=this.apple.device||this.android.device||this.other.device||this.seven_inch},v=i.isMobile=new l;v.Class=l})(window);

if(isMobile.any){ mobile = true; }

</script>
<script src="/assets/lib/fastclick/lib/fastclick.js"></script>
<script src="/assets/lib/hammer.js/hammer.min.js"></script>
<script src="/assets/js/bahn.js"></script>
<script src="/assets/js/blueimp-device-mask.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.0.5/jquery.lazyloadxt.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyloadxt/1.0.5/jquery.lazyloadxt.srcset.min.js"></script>

<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="/assets/lib/ie10-viewport-bug-workaround.js"></script>

<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="//pxr.intranet.deutschebahn.com/piwik/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 1]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="//pxr.intranet.deutschebahn.com/piwik/piwik.php?idsite=1" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->

</body>
