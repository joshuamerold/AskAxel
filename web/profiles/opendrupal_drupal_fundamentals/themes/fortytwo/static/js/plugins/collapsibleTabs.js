(function ($, Drupal) {
  Drupal.behaviors.collapsibleTabs = {
    attach: function (context, settings) {
      var body = $('body');
      var localTasks = $('#local-tasks, #tabs');

      if (body.hasClass('ft-tabs') && !body.hasClass('ft-tabs-rendered') && localTasks.length) {
        new CollapsibleTabs({
          el: localTasks
        })
      }
    },
  };

  function CollapsibleTabs(options) {
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
        view.tabs = view.el.clone();
        view.container = $('<div class="ft-collapsible-tabs"></div>');
        view.clicker = $('<span class="ft-tab-clicker"></span>');
        view.clicker.appendTo(view.container);

        view._render();
      },

      /**
       * Renders the grid to the correct dimensions
       */
      _render: function () {
        view.tabs.appendTo(view.container);
        view.container.appendTo('body');
        view.clicker.bind('click', this._clickAction);

        $('body').addClass('ft-tabs-rendered');
      },

      _clickAction: function() {
        view.container.toggleClass('ft-tabs-open');
      }
    };

    // Call the init function with provided options
    view.init(options);
    return view;
  }

}(jQuery, Drupal));
