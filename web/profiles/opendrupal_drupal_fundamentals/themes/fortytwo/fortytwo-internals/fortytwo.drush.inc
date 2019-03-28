<?php

/**
 * @file
 * Contains functions only needed for drush integration.
 */

/**
 * Implements hook_drush_command().
 */
function fortytwo_drush_command() {
  $items = array();

  $items['fortytwo'] = array(
    'description' => 'Create a sub theme using Fortytwo.',
    'arguments' => array(
      'name'         => 'A name for your theme.',
      'machine-name' => '[optional] A machine-readable name for your theme.',
      'description'  => '[optional] A description of your theme.',
    ),
    'options' => array(
      'name'         => 'A name for your theme.',
      'machine-name' => '[a-z, 0-9] A machine-readable name for your theme.',
      'description'  => 'A description of your theme.',
    ),
    'examples' => array(
      'drush fortytwo "My theme name"' => 'Create a sub-theme, using the default options.',
      'drush fortytwo "My theme name" my_theme' => 'Create a sub-theme with a specific machine and human name.',
      'drush fortytwo --name="Example theme" --machine-name=example --description "This is a description of my sub theme"' => 'Create a sub-theme with a specific description.',
    ),
  );

  return $items;
}

/**
 * Create a Fortytwo sub-theme using the starter kit.
 */
function drush_fortytwo($name = NULL, $machine_name = NULL) {
  // Determine the theme name.
  if (!isset($name)) {
    $name = drush_get_option('name');
  }

  if (empty($name)) {
    drush_set_error(dt("Please provide a name for the sub-theme.\nUSAGE:\tdrush fortytwo [name] --machine_name=[machine_name !OPTIONAL] --description=[description !OPTIONAL]\n"));
    return;
  }

  // Determine the machine name.
  if (!isset($machine_name)) {
    $machine_name = drush_get_option('machine-name');
  }
  if (!$machine_name) {
    $machine_name = $name;
  }
  $machine_name = str_replace(' ', '_', strtolower($machine_name));
  $search = array(
    // Remove characters not valid in function names.
    '/[^a-z0-9_]/',
    // Functions must begin with an alpha character.
    '/^[^a-z]+/',
  );
  $machine_name = preg_replace($search, '', $machine_name);

  // We want to have the theme installed in the themes/contrib folder.
  if (drupal_get_path('theme', 'fortytwo') != 'themes/contrib/fortytwo') {
    drush_die(dt('The fortytwo theme needs to be installed in the "themes/contrib/fortytwo" directory. Now it is installed in the "!directory". Please fix this issue, or create a manual copy and fix the scss imports.', array('!directory' => drupal_get_path('theme', 'fortytwo'))));
  }

  // Determine the path to the new subtheme by finding the path to Fortytwo.
  $fortytwo_path = drush_locate_root() . '/' . drupal_get_path('theme', 'fortytwo');
  drush_print(drupal_get_path('theme', 'fortytwo'));

  // Determine the path to the new subtheme by finding the path to Fortytwo.
  $subtheme_path = drush_locate_root() . '/themes/custom/' . str_replace('_', '-', $machine_name);

  // Make a fresh copy of the original starter kit.
  drush_op('fortytwo_copy', $fortytwo_path . '/STARTERKIT', $subtheme_path);

  // Rename the info.yml file.
  $subtheme_yml_info_file = $subtheme_path . '/' . $machine_name . '.info.yml';
  drush_op('rename', $subtheme_path . '/STARTERKIT.info.yml.txt', $subtheme_yml_info_file);

  // Rename the libraries.yml file.
  $subtheme_yml_lib_file = $subtheme_path . '/' . $machine_name . '.libraries.yml';
  drush_op('rename', $subtheme_path . '/STARTERKIT.libraries.yml', $subtheme_yml_lib_file);

  // Replace all occurrences of 'STARTERKIT' with the machine name of sub theme.
  drush_op('fortytwo_file_str_replace', $subtheme_yml_lib_file, 'STARTERKIT', $machine_name);

  // Alter the contents of the .info.yml file based on the command options.
  $alterations = array(
    ': Forty Two Sub-theme Starter Kit' => ': ' . $name,
    'STARTERKIT' => $machine_name,
  );
  $description = drush_get_option('description');
  if ($description) {
    $alterations['Forty Two Sub theme made from the starterkit'] = $description;
  }
  drush_op('fortytwo_file_str_replace', $subtheme_yml_info_file, array_keys($alterations), $alterations);

  // Rename the settings.yml file.
  $subtheme_settings_file = $subtheme_path . '/config/install/' . $machine_name . '.settings.yml';
  drush_op('rename', $subtheme_path . '/config/install/STARTERKIT.settings.yml', $subtheme_settings_file);

  // Rename the .theme file.
  $subtheme_theme_file = $subtheme_path . '/' . $machine_name . '.theme';
  drush_op('rename', $subtheme_path . '/STARTERKIT.theme', $subtheme_theme_file);

  // Replace all occurrences of 'STARTERKIT' with the machine name of sub theme.
  drush_op('fortytwo_file_str_replace', $subtheme_theme_file, 'STARTERKIT', $machine_name);

  // Rename the STARTERKIT.js file.
  $subtheme_js_file = $subtheme_path . '/static/js/' . $machine_name . '.js';
  drush_op('rename', $subtheme_path . '/static/js/STARTERKIT.js', $subtheme_js_file);

  // Replace all occurrences of 'STARTERKIT' with the machine name of sub theme.
  drush_op('fortytwo_file_str_replace', $subtheme_js_file, 'STARTERKIT', $machine_name);

  // Rename the STARTERKIT.scss file.
  $subtheme_js_file = $subtheme_path . '/static/sass/' . $machine_name . '.scss';
  drush_op('rename', $subtheme_path . '/static/sass/STARTERKIT.scss', $subtheme_js_file);

  // Rename the STARTERKIT.css file.
  $subtheme_js_file = $subtheme_path . '/static/css/' . $machine_name . '.css';
  drush_op('rename', $subtheme_path . '/static/css/STARTERKIT.css', $subtheme_js_file);

  // Replace all occurrences of 'STARTERKIT' with the machine name of sub theme.
  drush_op('fortytwo_file_str_replace', $subtheme_path . '/package.json', 'STARTERKIT', $machine_name);

  // Notify user of the newly created theme.
  drush_print(dt('Starter kit for "!name" created in: !path', array(
    '!name' => $name,
    '!path' => $subtheme_path,
  )));
}

/**
 * Copy a directory recursively.
 */
function fortytwo_copy($source_dir, $target_dir, $ignore = '/^(\.(\.)?|CVS|\.svn|\.git|\.DS_Store)$/') {
  if (!is_dir($source_dir)) {
    drush_die(dt('The directory "!directory" was not found.', array('!directory' => $source_dir)));
  }

  $folder = drush_mkdir($target_dir);
  if ($folder) {
    drush_copy_dir($source_dir, $target_dir, FILE_EXISTS_OVERWRITE);
  }
  else {
    drush_die(dt('The directory "!directory" could not be created.', array('!directory' => $target_dir)));
  }
}

/**
 * Replace strings in a file.
 */
function fortytwo_file_str_replace($file_path, $find, $replace) {
  $file_contents = file_get_contents($file_path);
  $file_contents = str_replace($find, $replace, $file_contents);
  file_put_contents($file_path, $file_contents);
}
