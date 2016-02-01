/* Config
------------------------------------------------------*/


/* Functions
------------------------------------------------------*/





/* Main
------------------------------------------------------*/

$(document).ready(function () {

  $(window).on("scroll touchmove", function () {
    $('.logo').toggleClass('tiny', $(document).scrollTop() > 80)
    $('.navbar').toggleClass('navbar-fixed-top', $(document).scrollTop() > 80);
  });

});
