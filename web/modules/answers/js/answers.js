(function($) {
  Drupal.behaviors.answersComments = {
    attach : function(context, settings) {
      jQuery('.node-answers-wrapper .comment-form', context).hide();
	  jQuery('.node-answers-wrapper .answers-comments-form-title', context).click(function(){
        $(this).hide().next('.node-answers-wrapper .comment-form').show('fast');
        return false;
      });
    }
  };
}(jQuery));
