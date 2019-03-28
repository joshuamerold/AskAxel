<?php

/**
 * @file
 * Contains functions needed for the theme.
 */

global $_fortytwo_path_to_core;
$_fortytwo_path_to_core = drupal_get_path('theme', 'fortytwo');

/**
 * Get theme functionalities.
 */
function fortytwo_get_theme(&$variables) {
  fortytwo_load_debuggers($variables);
  fortytwo_add_libraries($variables);
}

/**
 * Get the path to the static files folder in the theme.
 */
function fortytwo_get_staticpath($addprefix = FALSE, $theme_key = 'fortytwo') {
  $ret = '';
  if ($addprefix) {
    $ret = '/';
  }
  $ret .= drupal_get_path('theme', $theme_key) . '/static/';
  return $ret;
}

/**
 * Add libraries to the theme.
 */
function fortytwo_add_libraries(&$variables) {
  $enquire = theme_get_setting('ft_enquire_js');
  if (!empty($enquire)) {
    $variables['page']['#attached']['library'][] = 'fortytwo/enquire';
  }

  $transit = theme_get_setting('ft_transit_js');
  if (!empty($transit)) {
    $variables['page']['#attached']['library'][] = 'fortytwo/transit';
  }

  $placeholder = theme_get_setting('ft_placeholder_js');
  if (!empty($placeholder)) {
    $variables['page']['#attached']['library'][] = 'fortytwo/placeholder';
  }

  $tabs = theme_get_setting('ft_layout_tabs');
  if (!empty($tabs)) {
    $variables['page']['#attached']['library'][] = 'fortytwo/tabs';
  }

  $prettify = theme_get_setting('ft_prettify_js');
  if (!empty($prettify)) {
    $variables['page']['#attached']['drupalSettings']['fortytwo']['prettify_hide_upload'] = theme_get_setting('ft_prettify_js_hide_upload');
    $variables['page']['#attached']['drupalSettings']['fortytwo']['prettify_add_remove'] = theme_get_setting('ft_prettify_js_add_remove');
    $variables['page']['#attached']['library'][] = 'fortytwo/prettify';
  }
}

/**
 * Load debugging helper files.
 */
function fortytwo_load_debuggers(&$variables) {
  $debug = FALSE;
  // Load window size bookmarklet.
  if (theme_get_setting('ft_show_window_size') === 1) {
    $debug = TRUE;
    $variables['page']['#attached']['library'][] = 'fortytwo/windowsize';
  }
  if (theme_get_setting('ft_show_grid') === 1) {
    $debug = TRUE;
    $variables['page']['#attached']['library'][] = 'fortytwo/grid';
  }
  if ($debug) {
    drupal_set_message(t('Please turn off Forty Two debuggers on production websites.'), 'warning');
  }
}
