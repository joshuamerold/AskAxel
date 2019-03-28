<?php

/**
 * @file
 * bootstrap.inc
 *
 * Contains theme preprocessors to bootstrapize components.
 */

use Drupal\Component\Utility\Html;

require_once dirname(__FILE__) . '/fortytwo.inc';

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function fortytwo_theme_suggestions_page_alter(array &$suggestions, array $variables) {
  $route_match = \Drupal::routeMatch();

  // If on an individual node page, add the node type to theme suggestions.
  if ($node = $route_match->getParameter('node')) {
    $first_suggestion = array_shift($suggestions);
    array_unshift($suggestions, 'page__node__' . $node->bundle());
    if ($first_suggestion) {
      array_unshift($suggestions, $first_suggestion);
    }
    if (in_array('page__node__edit', $suggestions)) {
      $suggestions[] = 'page__node__edit__' . $node->bundle();
    }
  }
  else {
    // Add theme suggestions based on the route name.
    $route_name = $route_match->getRouteName();
    $split = explode('.', $route_name);
    $sug = 'page';
    foreach ($split as $suggestion) {
      $sug .= '__' . $suggestion;
      $suggestions[] = $sug;
    }
  }
}

/**
 * Implements hook_preprocess_html().
 */
function fortytwo_preprocess_html(array &$variables) {
  /* @var $user Drupal\Core\Session\AccountProxy */
  $user = $variables['user'];

  // Add a class to the body if a user is logged in or not.
  if ($user->id() > 0) {
    $classes[] = 'logged-in';
  }
  else {
    $classes[] = 'not-logged-in';
  }

  // Setup the theme basics.
  fortytwo_get_theme($variables);

  // Set some custom body classes.
  $classes[] = theme_get_setting('ft_layout_style');
  $classes[] = theme_get_setting('ft_layout_responsive');

  // Set sidebar classes.
  if (isset($variables['page']['sidebar_first']) && isset($variables['page']['sidebar_second'])) {
    $classes[] = 'two-sidebars';
  }
  elseif (isset($variables['page']['sidebar_first']) || isset($variables['page']['sidebar_second'])) {
    $classes[] = 'one-sidebar';
    if (isset($variables['page']['sidebar_first'])) {
      $classes[] = 'sidebar-first';
    }
    if (isset($variables['page']['sidebar_second'])) {
      $classes[] = 'sidebar-second';
    }
  }
  else {
    $classes[] = 'no-sidebars';
  }

  // Set class for the homepage.
  $is_front = \Drupal::service('path.matcher')->isFrontPage();
  if ($is_front) {
    $classes[] = 'front';
  }

  // Set debug grid classes.
  $variables['grid'] = theme_get_setting('ft_show_grid');
  ($variables['grid']) ? $classes[] = 'show-grid' : FALSE;

  // Set debug identifier classes.
  $variables['responsive_identifier'] = theme_get_setting('ft_show_responsive_identifier');
  ($variables['responsive_identifier']) ? $classes[] = 'show-responsive-identifier' : FALSE;

  // Set edit tabs.
  $variables['ft_layout_tabs'] = theme_get_setting('ft_layout_tabs');
  ($variables['ft_layout_tabs'] && $user->isAuthenticated()) ? $classes[] = 'ft-tabs' : FALSE;

  // Add the body classes to the attributes array.
  if (isset($variables['attributes']['class']) && is_array($variables['attributes']['class'])) {
    $variables['attributes']['class'] = array_merge($variables['attributes']['class'], $classes);
  }
  elseif (isset($variables['attributes']['class']) && is_a($variables['attributes']['class'], 'Drupal\Core\Template\AttributeArray')) {
    $variables['attributes']['class'] = array_merge($variables['attributes']['class']->value(), $classes);
  }
  else {
    $variables['attributes']['class'] = $classes;
  }

  // Change the mime type of the favicon to make it work in all browsers.
  $favicon = theme_get_setting('favicon');
  $type = 'image/x-icon';
  $variables['#attached']['html_head_link'][] = [
    [
      'rel' => 'shortcut icon',
      'href' => $favicon['url'],
      'type' => $type,
    ],
  ];
}

/**
 * Implements hook_preprocess_block().
 *
 * Clean up the div classes for blocks.
 */
function fortytwo_preprocess_block(array &$variables) {

  if (isset($variables['attributes']['id'])) {
    // Replace first occurrence of block- to clean up the ID's.
    if (0 === strpos($variables['attributes']['id'], 'block-')) {
      $variables['attributes']['id'] = substr($variables['attributes']['id'], 6);
    }

    $theme = \Drupal::theme()->getActiveTheme()->getName();
    if (FALSE !== strpos($variables['attributes']['id'], $theme)) {
      $variables['attributes']['id'] = str_replace($theme . '-', '', $variables['attributes']['id']);
    }
  }
}

/**
 * Override or insert variables into the node templates.
 *
 * @param array $variables
 *   An array of variables to pass to the theme template.
 * @param string $hook
 *   The name of the template being rendered ("node" in this case.)
 */
function fortytwo_preprocess_node(array &$variables, $hook) {
  /** @var \Drupal\node\Entity\Node $node */
  $node = $variables['node'];
  $function = __FUNCTION__ . '_' . $node->bundle();
  if (function_exists($function)) {
    $function($variables, $hook);
  }
}

/**
 * Override or insert variables into the field templates.
 *
 * @param array $variables
 *   An array of variables to pass to the theme template.
 * @param string $hook
 *   The name of the template being rendered ("node" in this case.)
 */
function fortytwo_preprocess_field(array &$variables, $hook) {
  $element = $variables['element'];

  // There's some overhead in calling check_plain() so only call it if the label
  // variable is being displayed. Otherwise, set it to NULL to avoid PHP
  // warnings if a theme implementation accesses the variable even when it's
  // supposed to be hidden. If a theme implementation needs to print a hidden
  // label, it needs to supply a preprocess function that sets it to the
  // sanitized element title or whatever else is wanted in its place.
  $variables['label_hidden'] = ($element['#label_display'] == 'hidden');
  if (empty($variables['label'])) {
    $variables['label'] = $variables['label_hidden'] ? NULL : Html::escape($element['#title']);
  }
}

/**
 * Implements hook_preprocess_breadcrumb().
 */
function fortytwo_preprocess_breadcrumb(&$variables) {
  $breadcrumb = &$variables['breadcrumb'];

  // Retrieve the breadcrumb settings.
  $show_breadcrumb_home = theme_get_setting('ft_breadcrumb_home');
  $breadcrumb_separator = theme_get_setting('ft_breadcrumb_separator');
  $add_trailing = theme_get_setting('ft_breadcrumb_trailing');
  $add_title = theme_get_setting('ft_breadcrumb_title');

  $variables['seperator'] = $breadcrumb_separator;
  $variables['trailing'] = $add_trailing;

  // Optionally get rid of the homepage link.
  if (!$show_breadcrumb_home) {
    array_shift($breadcrumb);
  }

  if ($add_title && !empty($breadcrumb)) {
    $request = \Drupal::request();
    $route_match = \Drupal::routeMatch();
    $page_title = \Drupal::service('title_resolver')
      ->getTitle($request, $route_match->getRouteObject());

    foreach ($breadcrumb as $key => &$crumb) {
      $crumb['icon'] = $breadcrumb_separator;
    }

    if (!empty($page_title)) {
      $breadcrumb[] = [
        'text' => $page_title,
        'icon' => $breadcrumb_separator,
      ];
    }

    end($breadcrumb);
    $lastkey = key($breadcrumb);

    if (empty($add_trailing) && $lastkey == $key) {
      unset($breadcrumb[$key]['icon']);
    }
  }
}

/**
 * Implements hook_page_attachments_alter().
 *
 * - Add meta tags to the html header.
 */
function fortytwo_page_attachments_alter(&$variables) {
  // Add viewport element.
  $viewport = theme_get_setting('ft_viewport');
  if (!empty($viewport)) {
    $user_scalable = theme_get_setting('ft_viewport_user_scaleable');
    $initial_scale = theme_get_setting('ft_viewport_initial_scale');
    $minimum_scale = theme_get_setting('ft_viewport_minimum_scale');
    $maximum_scale = theme_get_setting('ft_viewport_maximum_scale');
    $content = "width=device-width, initial-scale=" . $initial_scale . ", minimum-scale=" . $minimum_scale . ", maximum-scale=" . $maximum_scale . ", user-scalable=" . $user_scalable;

    $element = [
      [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'viewport',
          'content' => $content,
        ],
      ],
      'viewport',
    ];
    $variables['#attached']['html_head'][] = $element;
  }

  // Add cleartype.
  $cleartype = theme_get_setting('ft_cleartype');
  if (!empty($cleartype)) {
    $element = [
      [
        '#tag' => 'meta',
        '#attributes' => [
          'name' => 'http-equiv',
          'content' => 'on',
        ],
      ],
      'cleartype',
    ];

    $variables['#attached']['html_head'][] = $element;
  }
}
