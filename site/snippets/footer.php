    <!-- jQuery first, then Bootstrap JS. -->
    
    <script src="/assets/lib/jquery-2.2.0.min.js"></script>
<script src="../../assets/lib/isotope.pkgd.min.js"></script>
    <script src="/assets/lib/bootstrap/dist/js/bootstrap.js"></script>
    <script src="/assets/lib/lazy-load-xt/dist/jquery.lazyloadxt.min.js"></script>
    <script src="/assets/lib/lazy-load-xt/dist/jquery.lazyloadxt.srcset.min.js"></script>
    <script src="/assets/lib/picturefill.min.js"></script>
    <script src="/assets/js/mi.js"></script>
  
    <script>
    var $grid = $('.grid').isotope({
      itemSelector: '.modul',
      layoutMode: 'fitRows',
      getSortData: {
        name: '.modname', // text from querySelector
        category: '[data-category]'
      }
    });
    $grid.isotope({ sortBy : 'name' });
    </script>
  
  </body>
  
  
</html>
