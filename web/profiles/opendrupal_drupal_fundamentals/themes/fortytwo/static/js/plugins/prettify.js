(function (Drupal, $) {
  Drupal.ft_prettify = function Prettify(options) {
    var view = {

      defaults: {},

      init: function (options) {
        $.extend(view.defaults, options);
        view.item = $(options.el);
        view.callback = options.callback || null;
        view.count = 0;

        if (!view.item.hasClass('no-prettify')) {
          var tagname = options.el.tagName.toLowerCase();
          view._setEventHandlers();

          if (tagname == 'select') {
            view.type = 'select';
            view._renderSelect();
          }
          else if ((tagname == 'input') && (options.el.type == 'file')) {
            view.type = 'fileinput';
            view._renderFile();
          }
        }
      },

      activate: function () {
        view._setEventHandlers();
        view.label.show();
      },

      deactivate: function () {
        view.item.unbind('mouseenter blur change');
        view.label.hide();
      },

      _setEventHandlers: function () {
        view.item.bind('change', view._change);
        view.item.bind('focus', view._focus);
        view.item.bind('blur', view._blur);
        view.item.bind('mousedown', view._mousedown);
        view.item.bind('focusout', view._focusout);

        if ($('html').hasClass('lt-ie9')) {
          view.item.attr('data-width', view.item.outerWidth());
          view.item.bind('mouseenter', view._mouseEnter);
          view.item.bind('blur change', view._ieblur);
        }
      },

      _renderSelect: function () {
        view.item.wrap('<label class="select prettify"></label>');
        view.label = view.item.parent();

        if (view.item.is(':disabled')) {
          view.label.addClass('disabled');
        }

        if (view.item.hasClass('error')) {
          view.label.addClass('error');
        }

        var option = view.item.find('option:selected');
        var span = $('<span ' + view._getClass(option) + ' data-val="' + option.val() + '">' + option.text() + '</span>');

        view.item.after(span);
        view.item.fadeTo(0, 0);
        view.item.addClass('no-prettify');
      },

      _renderFile: function () {
        view.item.wrap('<label class="fileinput prettify"></label>');
        view.label = view.item.parent();
        view.label.bind('mousedown', view._mousedown);
        view.submit = view.label.siblings('.form-submit');

        if (view.item.is(':disabled')) {
          view.label.addClass('disabled');
        }

        if (view.item.hasClass('error')) {
          view.label.addClass('error');
        }

        var fake = $('<div><span><a class="button">' + Drupal.t('Browse') + '</a></span><span class="filetext">' + Drupal.t('No file selected') + '</span></div>');

        view.item.after(fake);
        view.item.fadeTo(0, 0);
        view.item.addClass('no-prettify');

        // Hide submit button by default.
        view.submit.hide();

        // Add button to clear file field.
        if (drupalSettings.fortytwo.prettify_add_remove) {
          view.clear = $('<a class="button fileclear">' + Drupal.t('Clear') + '</a>');
          view.clear.click(function () {
            view._clearFile();
          });

          view.submit.after(view.clear);
          view.clear.hide();
        }
      },

      _clearFile: function (event) {
        if (view.type == 'fileinput') {
          view.item.val('');
          view.label.find('span.filetext').removeClass('selected').text(view.emptyText);
          view.clear.hide();
          view.submit.hide();
        }
      },

      _change: function (event) {
        var el = $(this);

        if (view.type == 'fileinput') {
          var value = el.val();

          if (value) {
            value = value.substr(value.lastIndexOf('\\') + 1, value.length);
            view.label.find('span.filetext').addClass('selected').text(value);

            if (drupalSettings.fortytwo.prettify_add_remove) {
              view.clear.show();
            }

            if (!drupalSettings.fortytwo.prettify_hide_upload) {
              view.submit.show();
            }

          }
          else {
            view.label.find('span.filetext').removeClass('selected').text(view.emptyText);

            if (drupalSettings.fortytwo.prettify_add_remove) {
              view.clear.hide();
            }

            if (drupalSettings.fortytwo.prettify_hide_upload) {
              view.submit.hide();
            }
          }
        } else if (view.type == 'select') {
          var option = el.find('option:selected');
          el.parent().find('span').replaceWith('<span ' + view._getClass(option) + ' data-val="' + option.val() + '">' + option.text() + '</span>');

          if ($.isFunction(view.callback)) {
            view.callback.apply(null, [event, { url: option.attr('data-url') }]);
          }
        }
      },

      _mousedown: function (event) {
        view.lastClick = event.currentTarget;
      },

      _focus: function (event) {
        var target = '';

        if (view.type == 'fileinput') {
          target = $(event.target).parents('label').get(0);
          view.count += 1;
        }
        else {
          target = event.currentTarget;
        }

        if (target == view.lastClick) {
          view.label.addClass('hide-outline');
        }

        view.label.addClass('focus');
      },

      _focusout: function (event) {
        view.label.removeClass('hide-outline');

        if (view.type != 'fileinput' || view.count == 2) {
          view.lastClick = null;
          view.count = 0;
        }
      },

      _blur: function (event) {
        view.label.removeClass('focus');
      },

      _mouseEnter: function (event) {
        $(this).css('width', 'auto');
      },

      _ieblur: function (event) {
        $(this).css('width', $(this).attr('data-width'));
      },

      _getClass: function (option) {
        return option.attr('class') !== undefined ? 'class="' + option.attr('class') + '"' : '';
      },

    };

    view.init(options);

    return view;
  };

  $(function () {
    $('select:not(".no-prettify"), input[type="file"]:not(".no-prettify")').each(function () {
      new Drupal.ft_prettify({ el: this });
    });
  });
}(Drupal, jQuery));
