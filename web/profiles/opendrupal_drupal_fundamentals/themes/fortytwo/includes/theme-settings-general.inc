<?php
/**
 * @file
 * General theme settings elements.
 */

use Drupal\Core\Form\FormState;
use Drupal\Core\Url;

/**
 * Alter the theme settings form to add our own settings.
 *
 * @param array $form
 *   The form array passed by reference.
 * @param FormState $form_state
 *   The form state array.
 */
function fortytwo_form_system_theme_settings_alter(array &$form, FormState $form_state) {
  $form['fortytwo_settings'] = array(
    '#type' => 'vertical_tabs',
    '#weight' => -10,
    '#prefix' => '<h3>' . t('Configuration') . '</h3>',
  );

  fortytwo_theme_settings_general($form, $form_state);
}

/**
 * Setup the theme settings form.
 */
function fortytwo_theme_settings_general(&$form, &$form_state) {
  _fortytwo_theme_get_layout($form);
  _fortytwo_theme_get_javascript($form);
  _fortytwo_theme_get_metatags($form);
  _fortytwo_theme_get_debuggers($form);
  _fortytwo_theme_get_breadcrumb($form);
}

/**
 * Layout form fields.
 */
function _fortytwo_theme_get_layout(&$form) {
  $form['ft_layout'] = array(
    '#type'  => 'details',
    '#title' => t('Layout settings'),
    '#group' => 'fortytwo_settings',
  );

  $images = array(
    'columns-left'   => t('Columns left'),
    'columns-middle' => t('Columns middle'),
    'columns-right'  => t('Columns right'),
    'columns-none'   => t('No (one) columns'),
  );
  $form['ft_layout']['ft_layout_style'] = array(
    '#type'          => 'radios',
    '#title'         => t('Layout type'),
    '#description'   => t('Pick a layout type you want to use.'),
    '#default_value' => theme_get_setting('ft_layout_style'),
    '#options'       => $images,
  );

  $options = array(
    'layout-fixed'   => t('Fixed (not responsive)'),
    'Responsiveness' => array(
      'layout-adaptive' => t('Adaptive'),
      'layout-fluid'    => t('Fluid'),
    ),
  );

  $form['ft_layout']['ft_layout_responsive'] = array(
    '#type'          => 'select',
    '#title'         => t('Enable responsive styling'),
    '#default_value' => theme_get_setting('ft_layout_responsive'),
    '#options'       => $options,
  );

  $form['ft_layout']['ft_layout_tabs'] = array(
    '#type'          => 'checkbox',
    '#title'         => t('Use collapsible tabs'),
    '#description'   => t('The tabs will be collapsed on the right side of the page.'),
    '#default_value' => theme_get_setting('ft_layout_tabs'),
  );
}

/**
 * Javascript form fields.
 */
function _fortytwo_theme_get_javascript(&$form) {
  $form['ft_js'] = array(
    '#type' => 'details',
    '#title' => t('Javascript settings'),
    '#group' => 'fortytwo_settings',
  );
  $form['ft_js']['polyfills'] = array(
    '#type' => 'fieldset',
    '#title' => t('Polyfills'),
    '#prefix' => _fortytwo_theme_create_prefix(t('Polyfills')),
  );
  $form['ft_js']['polyfills']['ft_placeholder_js'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable placeholder.js'),
    '#description' => t('Enable the placeholder.js javascript plugin'),
    '#default_value' => theme_get_setting('ft_placeholder_js'),
  );
  $form['ft_js']['libraries'] = array(
    '#type' => 'fieldset',
    '#title' => t('Libraries'),
    '#prefix' => _fortytwo_theme_create_prefix(t('Libraries')),
  );
  $url = Url::fromUri('http://wicky.nillia.ms/enquire.js');
  $link = Drupal::l(t('enquire.js'), $url, array('attributes' => array('target' => '_blank')));
  $form['ft_js']['libraries']['ft_enquire_js'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable enquire.js'),
    '#description' => t('Enable the @link javascript plugin. This includes the matchMedia.js library', array('@link' => $link)),
    '#default_value' => theme_get_setting('ft_enquire_js'),
  );
  $url = Url::fromUri('http://ricostacruz.com/jquery.transit');
  $link = Drupal::l(t('transit.js'), $url, array('attributes' => array('target' => '_blank')));
  $form['ft_js']['libraries']['ft_transit_js'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable transit.js'),
    '#description' => t('Enable the @link javascript plugin', array('@link' => $link)),
    '#default_value' => theme_get_setting('ft_transit_js'),
  );
  $form['ft_js']['libraries']['ft_prettify_js'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable form field prettyfier'),
    '#description' => t('Prettifies fileinputs and selectboxes.'),
    '#default_value' => theme_get_setting('ft_prettify_js'),
  );
  $form['ft_js']['libraries']['ft_prettify_js_hide_upload'] = array(
    '#type' => 'checkbox',
    '#title' => t('Hide upload button on file fields'),
    '#description' => t('Since the files are uploaded when submitting a form, it is possible to hide the upload button.'),
    '#default_value' => theme_get_setting('ft_prettify_js_hide_upload'),
    '#states' => array(
      'visible' => array(
        ':input[name="ft_prettify_js"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['ft_js']['libraries']['ft_prettify_js_add_remove'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show clear button on file fields'),
    '#description' => t('After selecting a file a user needs to browser and cancel to deselect. The clear button will provide an easy way to clear the upload field.'),
    '#default_value' => theme_get_setting('ft_prettify_js_add_remove'),
    '#states' => array(
      'visible' => array(
        ':input[name="ft_prettify_js"]' => array('checked' => TRUE),
      ),
    ),
  );
}

/**
 * Meta tags form fields.
 */
function _fortytwo_theme_get_metatags(&$form) {
  $form['ft_metatags'] = array(
    '#type' => 'details',
    '#title' => t('Metatags and HTTP headers'),
    '#group' => 'fortytwo_settings',
  );

  $form['ft_metatags']['ft_cleartype'] = array(
    '#type' => 'checkbox',
    '#title' => t('Cleartype support for Windows'),
    '#description' => t('Mobile Internet Explorer allows us to activate ClearType technology for smoothing fonts for easy reading.'),
    '#default_value' => theme_get_setting('ft_cleartype'),
  );

  $form['ft_metatags']['ft_viewport'] = array(
    '#type' => 'checkbox',
    '#title' => t('Viewport'),
    '#description' => t('This is more widely supported by modern smartphones, including but not limited to: iOS, Android, Palm Pre, Blackberry, Windows Phone 7.'),
    '#default_value' => theme_get_setting('ft_viewport'),
  );

  $form['ft_metatags']['viewport_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Viewport settings'),
    '#states' => array(
      'visible' => array(
        ':input[name="ft_viewport"]' => array('checked' => TRUE),
      ),
    ),
  );

  $options = array('_none' => t('Default'));
  foreach (range(0.1, 10, 0.1) as $value) {
    // Format the value to display with one decimal.
    $value = number_format($value, 1);
    $options[(string) $value] = $value;
  }

  $form['ft_metatags']['viewport_settings']['ft_viewport_initial_scale'] = array(
    '#type' => 'select',
    '#title' => t('Initial scale'),
    '#default_value' => theme_get_setting('ft_viewport_initial_scale'),
    '#options' => $options,
    '#description' => t('The initial scaling of the page. Has to be in the range of the minimum- and maximum scale options.'),
  );

  $form['ft_metatags']['viewport_settings']['ft_viewport_user_scaleable'] = array(
    '#type' => 'checkbox',
    '#title' => t('User scalable'),
    '#default_value' => theme_get_setting('ft_viewport_user_scaleable'),
    '#description' => t("Whether users are allowed to zoom in and out. Defaults to 'yes'. This is usually accomplished via multi-touch gestures on iOS and Android devices. You should only disallow zooming (uncheck this checkbox) if your mobile theme is very customized and presented with good typography and graphics for a reduced mobile size."),
  );

  $form['ft_metatags']['viewport_settings']['scaling_settings'] = array(
    '#type' => 'fieldset',
    '#title' => t('Scaling settings'),
    '#states' => array(
      'visible' => array(
        ':input[name="ft_viewport_user_scaleable"]' => array('checked' => TRUE),
      ),
    ),
  );

  $form['ft_metatags']['viewport_settings']['scaling_settings']['ft_viewport_minimum_scale'] = array(
    '#type' => 'select',
    '#title' => t('Minimum scale'),
    '#default_value' => theme_get_setting('ft_viewport_minimum_scale'),
    '#options' => $options,
    '#description' => t('The minimum allowed scaling of the site.'),
  );

  $form['ft_metatags']['viewport_settings']['scaling_settings']['ft_viewport_maximum_scale'] = array(
    '#type' => 'select',
    '#title' => t('Maximum scale'),
    '#default_value' => theme_get_setting('ft_viewport_maximum_scale'),
    '#options' => $options,
    '#description' => t('The maximum allowed scaling of the site.'),
  );
}

/**
 * Debugger form fields.
 */
function _fortytwo_theme_get_debuggers(&$form) {
  $form['ft_debug'] = array(
    '#type' => 'details',
    '#title' => t('Debuggers'),
    '#group' => 'fortytwo_settings',
  );
  $form['ft_debug']['ft_show_window_size'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show window size'),
    '#descrption' => t('Appears in the bottom right corner'),
    '#default_value' => theme_get_setting('ft_show_window_size'),
  );

  $form['ft_debug']['ft_show_responsive_identifier'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show responsive identifier'),
    '#description' => t('Appears as a border on the top of the toolbar'),
    '#default_value' => theme_get_setting('ft_show_responsive_identifier'),
  );

  $form['ft_debug']['ft_show_grid'] = array(
    '#type'  => 'checkbox',
    '#title' => t('Show column grid(s)'),
    '#description' => t('matchMedia and SVG capable browser needed (Chrome, Safari or Firefox) to view the debug grid.'),
    '#default_value' => theme_get_setting('ft_show_grid'),
  );
}

/**
 * Breadcrumb form fields.
 */
function _fortytwo_theme_get_breadcrumb(&$form) {
  $form['ft_breadcrumb'] = array(
    '#type' => 'details',
    '#title' => t('Breadcrumb settings'),
    '#group' => 'fortytwo_settings',
  );
  $form['ft_breadcrumb']['breadcrumb_options'] = array(
    '#type' => 'container',
    '#states' => array(
      'invisible' => array(
        ':input[name="ft_breadcrumb"]' => array('value' => 'no'),
      ),
    ),
  );
  $form['ft_breadcrumb']['breadcrumb_options']['ft_breadcrumb_separator'] = array(
    '#type' => 'textfield',
    '#title' => t('Breadcrumb separator'),
    '#description' => t('Text only. Don’t forget to include spaces.'),
    '#default_value' => theme_get_setting('ft_breadcrumb_separator'),
    '#size' => 5,
    '#maxlength' => 10,
  );
  $form['ft_breadcrumb']['breadcrumb_options']['ft_breadcrumb_home'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show home page link in breadcrumb'),
    '#default_value' => theme_get_setting('ft_breadcrumb_home'),
  );
  $form['ft_breadcrumb']['breadcrumb_options']['ft_breadcrumb_trailing'] = array(
    '#type' => 'checkbox',
    '#title' => t('Append a separator to the end of the breadcrumb'),
    '#default_value' => theme_get_setting('ft_breadcrumb_trailing'),
    '#description' => t('Useful when the breadcrumb is placed just before the title.'),
    '#states' => array(
      'disabled' => array(
        ':input[name="ft_breadcrumb_title"]' => array('checked' => TRUE),
      ),
    ),
  );
  $form['ft_breadcrumb']['breadcrumb_options']['ft_breadcrumb_title'] = array(
    '#type' => 'checkbox',
    '#title' => t('Append the content title to the end of the breadcrumb'),
    '#default_value' => theme_get_setting('ft_breadcrumb_title'),
    '#description' => t('Useful when the breadcrumb is not placed just before the title.'),
  );
}

/**
 * A helper function to create a h3 tag.
 *
 * @param string $value
 *   The translated value for in the h3.
 *
 * @return string
 *   The h3 tag.
 */
function _fortytwo_theme_create_prefix($value) {
  $meta_generator = array(
    '#type' => 'html_tag',
    '#tag' => 'h3',
    '#value' => $value,
  );
  return drupal_render($meta_generator);
}
