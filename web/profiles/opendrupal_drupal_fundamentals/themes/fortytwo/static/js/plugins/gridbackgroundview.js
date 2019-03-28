(function ($, Drupal) {
  Drupal.behaviors.showGrid = {
    attach: function (context, settings) {
      // Activate gridBackgroundView only when show-grid is set on the body
      if ($('body').hasClass('show-grid')) {
        if (window.matchMedia) {
          new gridBackgroundView({
            el: $('#svg-grid-background')
          });
        }
        else {
          $('body').removeClass('show-grid');
          console.error(Drupal.t('Your browser does not support window.matchMedia, please use another browser for grid debugging.'));
        }
      }
    }
  };

  function gridBackgroundView(options) {
    var view = {

      /**
       * Default properties.
       */
      defaults: {
        layout: {
          adaptive: false,
          fluid: false,
        },
      },

      /**
       * Initialize this class.
       *
       * @param options Optional properties which override the defaults.
       */
      init: function (options) {
        $.extend(view.defaults, options);

        view.el = options.el;
        view.svg = view.el.find('svg');
        view.rects = view.svg.find('rect');

        view.MEDIA_QUERIES = Drupal.behaviors.fortytwoMain.constants.MEDIA_QUERIES;

        view._setLayoutType();
        view.gridconfig = view._getGridConfig();

        // set eventhandler for resizing the page.
        var layout = view.defaults.layout;
        if (layout.adaptive || layout.fluid) {
          $(window).bind('resize', view._resize);
        }

        view._render();
      },

      /**
       * Determines the layout type, adaptive, fluid or fixed.
       */
      _setLayoutType: function () {
        // store layout type in variable
        var body = $('body');
        if (body.hasClass('layout-adaptive')) {
          view.defaults.layout = {
            fluid: false,
            adaptive: true
          };
        }
        else if (body.hasClass('layout-fluid')) {
          view.defaults.layout = {
            fluid: true,
            adaptive: false
          };
        }
      },

      /**
       * Reads the config set in css and parses it to an object.
       */
      _getGridConfig: function () {
        // get the config from css
        var gridconfig = view.el.css('font-family');

        // Browsers differ in the return value they get,
        // do some string replacements before parsing
        gridconfig = gridconfig.indexOf('\'') ? gridconfig.replace(/\"/g, '').replace(/\'/g, '"') : gridconfig.replace(/\'/g, '').replace(/\\/g, '"');
        gridconfig = JSON.parse(gridconfig);

        if (gridconfig.columnWidth !== undefined) {
          return gridconfig;
        }
        else {
          // show error when JSON parsing did not work correctly
          console.error(Drupal.t('JSON PARSE ERROR'), gridconfig);
          return false;
        }
      },

      /**
       * Renders the grid to the correct dimensions
       */
      _render: function () {
        var amount = view.gridconfig.gutterWidth + view.gridconfig.columnWidth;
        var nonDesktop = !window.matchMedia(view.MEDIA_QUERIES.desktop).matches;
        var layout = view.defaults.layout;
        var x = 0;
        var columnWidth = 0;
        var diff = view.gridconfig.columns - view.rects.length;

        // If there are not enough rectangles in the svg, add them
        if (diff > 0) {
          for (var i = diff - 1; i >= 0; i--) {
            var cloned = view.rects[0].cloneNode(true);
            view.svg.get(0).appendChild(cloned);
          }

          // get the new array
          view.rects = view.svg.find('rect');
        }

        // Setting percentages
        if (layout.fluid && nonDesktop) {
          var percentages = view._getPercentages();

          // reset every fixed value to percentages
          view.svg.width(percentages.canvasWidth + '%');
          amount = percentages.width + percentages.gutter;
          columnWidth = percentages.width + '%';

        } else {
          view.svg.css('width', '');
          columnWidth = view.gridconfig.columnWidth;
        }

        // set the height of the document
        view.svg.height($(document).height());

        // each rectangle in the svg gets its new values
        view.rects.each(function (i, item) {

          // cannot set svg stuff with jQuery, so get the DOM node
          var rect = $(item).get(0);

          // because we need to re-calculate the amount value for
          // every column (x += amount), setting the percentage
          // is done here
          var xPos = (layout.fluid && nonDesktop) ? x.toFixed(5) + '%' : x;
          rect.setAttributeNS(null, 'width', columnWidth);
          rect.setAttributeNS(null, 'x', xPos);
          x += amount;
        });
      },

      /**
       * When resizing the page the gridconfig is checked, if
       * changed it calls the _render method.
       */
      _resize: function (event) {
        console.log('RESIZING');
        var gridconfig = view._getGridConfig();

        // only re-render when gridconfigs differ.
        if (JSON.stringify(gridconfig) !== JSON.stringify(view.gridconfig)) {
          view.gridconfig = gridconfig;
          view._render();
        }
      },

      /**
       * Calculates the percentage dimensions for the fluid grid.
       */
      _getPercentages: function () {
        // contextWidth is the width that is used as the 100% reference
        var contextWidth = (view.gridconfig.columnWidth + view.gridconfig.gutterWidth) * view.gridconfig.columns - view.gridconfig.gutterWidth;
        var oneColumnWidth = (view.gridconfig.columnWidth + view.gridconfig.gutterWidth) * 1 - view.gridconfig.gutterWidth;
        var targetWidth = (view.gridconfig.columnWidth * view.gridconfig.columns) + (view.gridconfig.gutterWidth * (view.gridconfig.columns - 1));
        var wrapperMargin = view.gridconfig.columns * 2;

        var newColumnWidth = parseFloat(((100 * oneColumnWidth) / contextWidth).toFixed(5));
        var newGutter = parseFloat(((100 * view.gridconfig.gutterWidth) / contextWidth).toFixed(5));
        var canvasWidth = parseFloat((((100 * targetWidth) / contextWidth) - ((100 * wrapperMargin) / view.gridconfig.masterContextWidth)).toFixed(5));

        return {
          width: newColumnWidth,
          gutter: newGutter,
          canvasWidth: canvasWidth
        };
      }
    };

    // Call the init function with provided options
    view.init(options);
    return view;
  }

}(jQuery, Drupal));
