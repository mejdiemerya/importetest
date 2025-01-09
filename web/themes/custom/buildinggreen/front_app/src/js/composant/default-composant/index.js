
/**
 * @file
 * Custom customjs.
 */

var mobileMaxWidth = '768';
var isMobile = window.matchMedia("only screen and (max-width: " + mobileMaxWidth + "px)").matches;

(function ($) {
  Drupal.behaviors.customjs = {
    attach: function (context, settings) {
      //$( "#bg-leeduser-search-form select" ).selectmenu();
      $( "#bg-leeduser-search-form select" ).select2({
        minimumResultsForSearch: 20,
      });

      $(document).ready(function () {
        //$( "#bg-leeduser-search-form select" ).selectmenu();
        // console.log('hekk');
        $('.nav-pills a').click(function (e) {
          e.preventDefault();
          $('.nav-pills li').removeClass('active');
          $('.tab-pane').removeClass('active');
          $(this).parent().addClass('active');
          $($(this).attr('href')).addClass('active');
        });
      });




    }
  }
})(jQuery);
