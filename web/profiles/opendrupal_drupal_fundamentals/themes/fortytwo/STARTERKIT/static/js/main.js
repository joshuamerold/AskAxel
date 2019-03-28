(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.app = {
    attach: function (context, settings) {
      constants = Drupal.behaviors.fortytwoMain.constants;

      // Store responsive type
      var body = $('body');
      if (body.hasClass('layout-adaptive')) constants.LAYOUT = {
        fluid: false,
        adaptive: true
      };
      else if (body.hasClass('layout-fluid')) constants.LAYOUT = {
        fluid: true,
        adaptive: false
      };

      // Transit.js will fallback to frame based animation when transitions aren't supported
      // if( !$("html").hasClass( "csstransitions" )) $.fn.transition = $.fn.animate;
    }
  };

})(jQuery, Drupal, this, this.document);
