<?php

/**
 * @file
 * Theme an answers_question node.
 *
 * Available variables:
 * - $title: the (sanitized) title of the node.
 * - $content: An array of node items. Use render($content) to print them all,
 *   or print a subset such as render($content['field_example']). Use
 *   hide($content['field_example']) to temporarily suppress the printing of a
 *   given element.
 * - $user_picture: The node author's picture from user-picture.tpl.php.
 * - $date: Formatted creation date. Preprocess functions can reformat it by
 *   calling format_date() with the desired parameters on the $created variable.
 * - $name: Themed username of node author output from theme_username().
 * - $node_url: Direct url of the current node.
 * - $display_submitted: Whether submission information should be displayed.
 * - $submitted: Submission information created from $name and $date during
 *   template_preprocess_node().
 * - $classes: String of classes that can be used to style contextually through
 *   CSS. It can be manipulated through the variable $classes_array from
 *   preprocess functions. The default values can be one or more of the
 *   following:
 *   - node: The current template type, i.e., "theming hook".
 *   - node-[type]: The current node type. For example, if the node is a
 *     "Blog entry" it would result in "node-blog". Note that the machine
 *     name will often be in a short form of the human readable label.
 *   - node-teaser: Nodes in teaser form.
 *   - node-preview: Nodes in preview mode.
 *   The following are controlled through the node publishing options.
 *   - node-promoted: Nodes promoted to the front page.
 *   - node-sticky: Nodes ordered above other non-sticky nodes in teaser
 *     listings.
 *   - node-unpublished: Unpublished nodes visible only to administrators.
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 *
 * Other variables:
 * - $node: Full node object. Contains data that may not be safe.
 * - $type: Node type, i.e. story, page, blog, etc.
 * - $comment_count: Number of comments attached to the node.
 * - $uid: User ID of the node author.
 * - $created: Time the node was published formatted in Unix timestamp.
 * - $classes_array: Array of html class attribute values. It is flattened
 *   into a string within the variable $classes.
 * - $zebra: Outputs either "even" or "odd". Useful for zebra striping in
 *   teaser listings.
 * - $id: Position of the node. Increments each time it's output.
 *
 * Node status variables:
 * - $view_mode: View mode, e.g. 'full', 'teaser'...
 * - $teaser: Flag for the teaser state (shortcut for $view_mode == 'teaser').
 * - $page: Flag for the full page state.
 * - $promote: Flag for front page promotion state.
 * - $sticky: Flags for sticky post setting.
 * - $status: Flag for published status.
 * - $comment: State of comment settings for the node.
 * - $readmore: Flags true if the teaser content of the node cannot hold the
 *   main body content.
 * - $is_front: Flags true when presented in the front page.
 * - $logged_in: Flags true when the current user is a logged-in member.
 * - $is_admin: Flags true when the current user is an administrator.
 *
 * Field variables: for each field instance attached to the node a corresponding
 * variable is defined, e.g. $node->body becomes $body. When needing to access
 * a field's raw values, developers/themers are strongly encouraged to use these
 * variables. Otherwise they will have to explicitly specify the desired field
 * language, e.g. $node->body['en'], thus overriding any language negotiation
 * rule that was previously applied.
 *
 * @see template_preprocess()
 * @see template_preprocess_node()
 * @see template_process()
 */
?>

<?php
  // Remove the "Add new comment" link on the teaser page or if the comment.
  unset($content['links']['comment']['#links']['comment-add']);
?>

<?php

  // Hide these items to render when we choose.
  hide($content['links']['statistics']);
  hide($content['comments']);
  hide($content['links']);
  hide($content['best_answer']);
  hide($content['answers_list']);
  hide($content['new_answer_form']);
?>



<div class="node-answers-wrapper">
  <div id="node-<?php print $node->nid; ?>" class="<?php print $classes; ?> clearfix" <?php print $attributes; ?>>
    <?php print render($title_prefix); ?>
    <?php if (!$page) : ?>
      <h2<?php print $title_attributes; ?>>
        <a href="<?php print $node_url; ?>"><?php print $title; ?></a>
      </h2>
    <?php endif; ?>
    <?php print render($title_suffix); ?>
    <div class="answers-widgets-wrapper">
      <div class="answers-submitted">
        <?php print $user_picture; ?>
        <div class="author-name"><?php print $name; ?></div>
        <?php if (module_exists('answers_userpoints')) : ?>
          <div class="author-details">
            <p class="author-points">
                <?php
                print userpoints_get_current_points($node->uid);
                print ' ' . t('!points', userpoints_translation());
                ?>
            </p>
          </div>
        <?php endif; ?>
      </div>
      <div class="answers-widgets">
        <div class="mystery-hack"></div>
        <?php
        if (isset($content['best_answer'])) :
            print render($content['best_answer']);
        endif;
        ?>
        <?php
        if (isset($content['answersRateWidget'])) :
            print render($content['answersRateWidget']);
        endif;
        ?>
      </div>
    </div>
    <div class="answers-body-wrapper">
      <div class="answers-body">
        <div class="content clearfix" <?php print $content_attributes; ?>>
            <?php print render($content); ?>
          <span class="submitted-time">
            <?php print t('Posted') . ' ' . format_interval(time() - $node->created, 1) . ' ' . t('ago.'); ?>
          </span>
            <?php
            if (module_exists('statistics')) {
                $statistics = statistics_get($node->nid);
                if ($statistics['totalcount'] > 0) {
                    print '<span class="views">';
                    print format_plural($statistics['totalcount'], '1 view.', '@count views.');
                    print '</span>';
                }
            }
            ?>
        </div>
      </div>

      <div class="answers-body-toolbar">
        <?php if (isset($content['new_answer_form']) && $content['new_answer_form']['#node_edit_form']) : ?>
        <a id="answers-btn-answer" class="answers-btn-primary" href="#new-answer-form">Answer</a>
        <?php
        endif;

        $links = render($content['links']);
if ($links) :
        ?>
          <div class="link-wrapper">
            <?php
            print $links;
            if (user_access('post comments')) {
              // Add a "pseudo-link" to open the comment dialog.
              // This is done using jquery.
                print '<ul class="links inline"><li class="answers-comment-button"><a>Comment</a></li></ul>';
            }
            ?>
          </div>
<?php endif; ?>
      </div>
        <?php print render($content['comments']); ?>
    </div>
  </div>

    <?php if (isset($content['answers_list'])) : ?>
    <div class="answers-list">
        <?php print render($content['answers_list']); ?>
    </div>
    <?php endif;?>

    <?php if (isset($content['new_answer_form'])) : ?>
    <div id="new-answer-form">
        <?php print render($content['new_answer_form']); ?>
    </div>
    <?php endif;?>
</div>
