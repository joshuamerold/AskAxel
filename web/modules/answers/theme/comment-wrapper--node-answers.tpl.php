<?php

/**
 * @file
 * Themes comment wrappers.
 */
?>
<div class="answersComments <?php print $classes; ?>"<?php print $attributes; ?>>
  <?php if ($content['comments'] && $node->type != 'forum'): ?>
    <?php print render($title_prefix); ?>
    <h2 class="answers-comments-title title"><?php print t('Comments'); ?></h2>
    <?php print render($title_suffix); ?>
  <?php endif; ?>

  <?php print render($content['comments']); ?>

  <?php if ($content['comment_form']): ?>
    <a class="answers-comments-form-title"><?php print t('Add new comment'); ?></a>
    <?php print render($content['comment_form']); ?>
  <?php endif; ?>
</div>
