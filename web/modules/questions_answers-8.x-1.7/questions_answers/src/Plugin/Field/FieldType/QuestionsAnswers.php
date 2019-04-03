<?php

namespace Drupal\questions_answers\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\questions_answers\Entity\Question;

/**
 * Provides a field type of Questions and Answers.
 *
 * @FieldType(
 *   id = "questions_answers",
 *   label = @Translation("Questions and Answers"),
 *   module = "questions_answers",
 *   category = @Translation("Questions and Answers"),
 *   description = @Translation("A user-powered questions and answers field."),
 *   default_formatter = "questions_answers_formatter",
 *   default_widget = "questions_answers_widget",
 * )
 */
class QuestionsAnswers extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      // Columns contain the values that the field will store.
      'columns' => [
        'question_id' => [
          'type' => 'int',
          'not null' => TRUE,
          'default' => 0,
          'description' => 'The entity id of the associated question entity',
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = [];
    $properties['question_id'] = DataDefinition::create('integer')
      ->setLabel(t('Question ID'))
      ->setDescription(t('The entity id of the associated question entity'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $question_id = $this->get('question_id')->getValue();
    return $question_id === NULL || $question_id === '';
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Load the question entity that is associated.
    $question_id = $this->get('question_id')->getValue();
    $bundle = $this->getFieldDefinition()->getTargetEntityTypeId();
    // Load the question.
    $question_ids = \Drupal::entityQuery('questions_answers_question')
      ->condition('bundle', $bundle, '=')
      ->condition('id', $question_id, '=')
      ->execute();
    if (count($question_ids) > 0) {
      $question = Question::load(array_pop($question_ids));
      // Loop over the answer entities.
      foreach ($question->getAnswers() as $answer) {
        // Delete them.
        $answer->delete();
      }
      // Delete the question.
      $question->delete();
    }
  }

}
