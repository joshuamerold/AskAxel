<?php

namespace Drupal\questions_answers\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display parent entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("questions_answers_parententity")
 */
class ParentEntity extends FieldPluginBase {

  /**
   * Additional query data.
   */
  public function query() {}

  /**
   * Define the available options.
   *
   * @return array
   *   Array of available options.
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    return $options;
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

    return $entity->getEntity()->toLink()->toString();
  }

}
