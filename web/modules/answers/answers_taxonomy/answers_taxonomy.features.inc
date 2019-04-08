<?php

/**
 * @file
 * answers_taxonomy.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function answers_taxonomy_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "taxonomy_display" && $api == "taxonomy_display") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function answers_taxonomy_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
