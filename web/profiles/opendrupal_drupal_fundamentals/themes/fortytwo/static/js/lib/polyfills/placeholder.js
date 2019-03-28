(function ($) {

  function Placeholder(element) {

    var view = {

      init: function (item) {
        item = $(item);

        // attach event handlers
        item.bind('focus', view.hide);
        item.bind('blur', view.show);

        view.render(item);
      },

      render: function (item) {
        if (item.attr('type') == 'password') {
          view.clone(item);
        }

        view.show(item);
      },

      show: function (e) {
        var $el = (typeof e.currentTarget != 'undefined') ? $(e.currentTarget) : $(e);

        if (_.isUndefined($el.attr('value')) || $el.attr('value') === $el.attr('placeholder')) {
          if ($el.attr('type') == 'password') {
            $el.hide();
            $el.next().show();
          } else {
            $el.val($el.attr('placeholder'));
            $el.addClass('placeholder');
          }
        }
      },

      hide: function (e) {
        var $el = $(e.currentTarget);

        if ($el.val() == $el.attr('placeholder') && $el.hasClass('placeholder')) {
          $el.val('');
          $el.removeClass('placeholder');
        }
      },

      clone: function (item) {
        var copy = $(document.createElement('input'));

        copy.addClass('placeholder');
        copy.val(item.attr('placeholder'));
        copy.attr({
          type: 'text',
          name: 'ph-clone-' + item.attr('name'),
        });

        copy.on('focus', function () {
          $(this).hide();
          item.show();
          setTimeout(function () { item.focus(); }, 1);
        });

        copy.insertAfter(item);
      },
    };

    view.init(element);

    return view;
  }

  $('[placeholder]').each(function () { new Placeholder({ el: this }); });

  // window.Placeholder = Placeholder;

})(jQuery);
