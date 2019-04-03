<?php

namespace Drupal\questions_answers\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler to display a list of answers.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("questions_answers_answerlist")
 */
class AnswerList extends FieldPluginBase {

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

    $answers = [
      '#theme' => 'item_list',
      '#items' => [],
      '#type' => 'ul',
    ];
    foreach ($entity->getAnswers() as $answer) {
      $answers['#items'][] = $answer->getValue();
    }

    return $answers;
  }

}
