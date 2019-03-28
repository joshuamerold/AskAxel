(function ($, Drupal, window, document, undefined) {

  Drupal.behaviors.fortytwoMain = {
    constants: {
      LAYOUT: {
        adaptive: false,
        fluid: false,
      },

      MEDIA_QUERIES: {
        desktop: 'all and (min-width: 1230px)',

        tablet: {
          both: 'all and (min-width: 768px) and (max-width: 1229px)',
          portrait: 'all and (min-width: 768px) and (max-width: 1001px)',
          landscape: 'all and (min-width: 1002px) and (max-width: 1247px)',
        },

        mobile: {
          both: 'all and (min-width: 0px) and (max-width: 767px)',
          portrait: 'all and (min-width: 0px) and (max-width: 479px)',
          landscape: 'all and (min-width: 480px) and (max-width: 767px)',
        },
      },
    },
  };

})(jQuery, Drupal, this, this.document);
