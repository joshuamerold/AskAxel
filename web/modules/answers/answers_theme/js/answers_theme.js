(function($) {
  Drupal.behaviors.answersComments = {
    attach : function(context, settings) {
      // Attach the function to a button attached to a question or an answer
	  jQuery('.answers-comment-button', context).click(function(){
        form =  $(this).parent().parent().parent().next('.comment-wrapper').children('.comment-form');
        form.show('fast');
        return false;
      });
     // Attach to a button attached to a comment
     jQuery('.answersComments .comment-reply', context).click(function(){
        form =  $(this).parent().parent().parent().parent().parent().children('.comment-form');
        form.show('fast');
        return false;
      });
     // Show the filter help when help button clicked
     jQuery('.answers-form-filter-help', context).click(function(){
        form =  $(this).parent().parent().parent().children('.filter-wrapper');
        form.show('fast');
        return false;
      });
    }
  };
}(jQuery));
