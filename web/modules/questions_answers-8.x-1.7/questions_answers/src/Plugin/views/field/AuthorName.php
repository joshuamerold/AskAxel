<?php

namespace Drupal\questions_answers\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display author name.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("questions_answers_author")
 */
class AuthorName extends FieldPluginBase {

  /**
   * Additional query data.
   */
  public function query() {
    $this->ensureMyTable();
  }

  /**
   * Define the available options.
   *
   * @return array
   *   Array of available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['display_link'] = ['default' => FALSE];

    return $options;
  }

  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display_link'] = [
      '#title' => $this->t('Display as Link?'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['display_link'],
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * Render the field.
   *
   * @param \Drupal\views\ResultRow $values
   *   The result row we are rendering the value for.
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;

    // Use the relationship entity if this is one.
    if ($this->options['table'] == 'questions_answers_answer') {
      foreach ($values->_relationship_entities as $r_entity) {
        $entity = $r_entity;
      }
    }

    if ($this->options['display_link']) {
      return $entity->getOwner()->toLink()->toString();
    }

    return $entity->getAuthorName();
  }

}
